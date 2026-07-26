<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BillControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_bill_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/bills')->assertForbidden();
    }

    public function test_it_lists_bills_for_the_current_fiscal_year_by_default(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_name' => 'Test Dept']);

        $bill = Bill::create([
            'invoice_no' => 999001, 'subject' => 'In range', 'bill_date' => now()->toDateString(),
            'd_id' => $department->id, 'total' => 0, 'gst_bill' => 1, 'paid' => 0,
        ]);

        $response = $this->actingAs($user)->get('/bills');

        $response->assertOk();
        $response->assertSee($bill->subject);
    }

    public function test_it_creates_a_bill_with_line_items_and_computes_the_total_server_side(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/bills', [
            'subject' => 'Test bill',
            'bill_date' => now()->toDateString(),
            'gst_bill' => 1,
            'paid' => 0,
            'items' => [
                ['product_name' => 'Item A', 'price' => 100, 'qty' => 2, 'total' => 999999], // client total ignored
                ['product_name' => 'Item B', 'price' => 50, 'qty' => 1, 'total' => 50],
            ],
        ]);

        $bill = Bill::where('subject', 'Test bill')->firstOrFail();

        $response->assertRedirect('/bills');
        $this->assertEquals(250.00, $bill->total);
        $this->assertCount(2, $bill->items()->get());
        $this->assertSame('Item A', $bill->items()->orderBy('sort_order')->first()->product_name);
    }

    public function test_it_rejects_a_bill_with_no_line_items(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/bills', [
            'subject' => 'No items',
            'bill_date' => now()->toDateString(),
            'gst_bill' => 1,
            'paid' => 0,
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('bill', ['subject' => 'No items']);
    }

    public function test_updating_a_bill_replaces_its_line_items(): void
    {
        $user = User::factory()->create();
        $bill = Bill::create([
            'subject' => 'Original', 'bill_date' => now()->toDateString(),
            'total' => 100, 'gst_bill' => 1, 'paid' => 0,
        ]);
        $bill->items()->create(['product_name' => 'Old item', 'price' => 100, 'qty' => 1, 'total' => 100, 'sort_order' => 0]);

        $response = $this->actingAs($user)->put("/bills/{$bill->id}", [
            'subject' => 'Updated',
            'bill_date' => now()->toDateString(),
            'gst_bill' => 0,
            'paid' => 1,
            'items' => [
                ['product_name' => 'New item', 'price' => 40, 'qty' => 3, 'total' => 120],
            ],
        ]);

        $response->assertRedirect('/bills');
        $bill->refresh();
        $this->assertSame('Updated', $bill->subject);
        $this->assertEquals(120.00, $bill->total);
        $items = $bill->items()->get();
        $this->assertCount(1, $items);
        $this->assertSame('New item', $items[0]->product_name);
    }

    public function test_deleting_a_bill_also_removes_its_line_items(): void
    {
        $user = User::factory()->create();
        $bill = Bill::create(['subject' => 'To delete', 'bill_date' => now()->toDateString(), 'total' => 100, 'gst_bill' => 1, 'paid' => 0]);
        $item = $bill->items()->create(['product_name' => 'Item', 'price' => 100, 'qty' => 1, 'total' => 100, 'sort_order' => 0]);

        $response = $this->actingAs($user)->delete("/bills/{$bill->id}");

        $response->assertRedirect('/bills');
        $this->assertDatabaseMissing('bill', ['id' => $bill->id]);
        $this->assertDatabaseMissing('bill_items', ['id' => $item->id]);
    }

    public function test_print_view_computes_gst_and_grand_total(): void
    {
        $user = User::factory()->create();
        $bill = Bill::create(['subject' => 'Printable', 'bill_date' => now()->toDateString(), 'total' => 200, 'gst_bill' => 1, 'paid' => 0]);
        $bill->items()->create(['product_name' => 'Item', 'price' => 200, 'qty' => 1, 'total' => 200, 'sort_order' => 0]);

        $response = $this->actingAs($user)->get("/bills/{$bill->id}/print");

        $response->assertOk();
        $response->assertSee('236.00', false);
        $response->assertSee('Two Hundred Thirty Six Rupees Only');
    }

    public function test_print_view_skips_gst_when_bill_is_not_a_gst_bill(): void
    {
        $user = User::factory()->create();
        $bill = Bill::create(['subject' => 'No GST', 'bill_date' => now()->toDateString(), 'total' => 200, 'gst_bill' => 0, 'paid' => 0]);
        $bill->items()->create(['product_name' => 'Item', 'price' => 200, 'qty' => 1, 'total' => 200, 'sort_order' => 0]);

        $response = $this->actingAs($user)->get("/bills/{$bill->id}/print");

        $response->assertOk();
        $response->assertSee('Two Hundred Rupees Only');
        $response->assertDontSee('CGST @');
    }

    public function test_pdf_endpoint_downloads_a_pdf(): void
    {
        $user = User::factory()->create();
        $bill = Bill::create(['subject' => 'PDF Bill', 'bill_date' => now()->toDateString(), 'total' => 200, 'gst_bill' => 1, 'paid' => 0]);
        $bill->items()->create(['product_name' => 'Item', 'price' => 200, 'qty' => 1, 'total' => 200, 'sort_order' => 0]);

        $response = $this->actingAs($user)->get("/bills/{$bill->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_pdf_endpoint_is_forbidden_without_print_permission(): void
    {
        $user = User::factory()->subAdmin(['Bill', 'Add New Bill', 'Edit Bill', 'Delete Bill'])->create();
        $bill = Bill::create(['subject' => 'PDF Bill', 'bill_date' => now()->toDateString(), 'total' => 200, 'gst_bill' => 1, 'paid' => 0]);

        $this->actingAs($user)->get("/bills/{$bill->id}/pdf")->assertForbidden();
    }
}
