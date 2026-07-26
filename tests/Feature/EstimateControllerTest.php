<?php

namespace Tests\Feature;

use App\Models\Estimate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EstimateControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_estimate_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/estimates')->assertForbidden();
    }

    public function test_it_creates_an_estimate_with_line_items_and_computes_the_total_server_side(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/estimates', [
            'subject' => 'Test estimate',
            'bill_date' => now()->toDateString(),
            'items' => [
                ['product_name' => 'Item A', 'price' => 100, 'qty' => 2, 'total' => 999999], // client total ignored
                ['product_name' => 'Item B', 'price' => 50, 'qty' => 1, 'total' => 50],
            ],
        ]);

        $estimate = Estimate::where('subject', 'Test estimate')->firstOrFail();

        $response->assertRedirect('/estimates');
        $this->assertEquals(250.00, $estimate->total);
        $this->assertCount(2, $estimate->items()->get());
    }

    public function test_it_rejects_an_estimate_with_no_line_items(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/estimates', [
            'subject' => 'No items',
            'bill_date' => now()->toDateString(),
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('estimate', ['subject' => 'No items']);
    }

    public function test_updating_an_estimate_replaces_its_line_items(): void
    {
        $user = User::factory()->create();
        $estimate = Estimate::create(['subject' => 'Original', 'bill_date' => now()->toDateString(), 'total' => 100]);
        $estimate->items()->create(['product_name' => 'Old item', 'price' => 100, 'qty' => 1, 'total' => 100, 'sort_order' => 0]);

        $response = $this->actingAs($user)->put("/estimates/{$estimate->id}", [
            'subject' => 'Updated',
            'bill_date' => now()->toDateString(),
            'items' => [
                ['product_name' => 'New item', 'price' => 40, 'qty' => 3, 'total' => 120],
            ],
        ]);

        $response->assertRedirect('/estimates');
        $estimate->refresh();
        $this->assertSame('Updated', $estimate->subject);
        $this->assertEquals(120.00, $estimate->total);
        $items = $estimate->items()->get();
        $this->assertCount(1, $items);
        $this->assertSame('New item', $items[0]->product_name);
    }

    public function test_deleting_an_estimate_also_removes_its_line_items(): void
    {
        $user = User::factory()->create();
        $estimate = Estimate::create(['subject' => 'To delete', 'bill_date' => now()->toDateString(), 'total' => 100]);
        $item = $estimate->items()->create(['product_name' => 'Item', 'price' => 100, 'qty' => 1, 'total' => 100, 'sort_order' => 0]);

        $response = $this->actingAs($user)->delete("/estimates/{$estimate->id}");

        $response->assertRedirect('/estimates');
        $this->assertDatabaseMissing('estimate', ['id' => $estimate->id]);
        $this->assertDatabaseMissing('estimate_items', ['id' => $item->id]);
    }

    public function test_print_view_always_applies_gst(): void
    {
        $user = User::factory()->create();
        $estimate = Estimate::create(['subject' => 'Printable', 'bill_date' => now()->toDateString(), 'total' => 200]);
        $estimate->items()->create(['product_name' => 'Item', 'price' => 200, 'qty' => 1, 'total' => 200, 'sort_order' => 0]);

        $response = $this->actingAs($user)->get("/estimates/{$estimate->id}/print");

        $response->assertOk();
        $response->assertSee('236.00', false);
        $response->assertSee('Two Hundred Thirty Six Rupees Only');
    }

    public function test_pdf_endpoint_downloads_a_pdf(): void
    {
        $user = User::factory()->create();
        $estimate = Estimate::create(['subject' => 'PDF Estimate', 'bill_date' => now()->toDateString(), 'total' => 200]);
        $estimate->items()->create(['product_name' => 'Item', 'price' => 200, 'qty' => 1, 'total' => 200, 'sort_order' => 0]);

        $response = $this->actingAs($user)->get("/estimates/{$estimate->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_excel_endpoint_downloads_the_line_items(): void
    {
        \Maatwebsite\Excel\Facades\Excel::fake();

        $user = User::factory()->create();
        $estimate = Estimate::create(['subject' => 'Excel Estimate', 'bill_date' => now()->toDateString(), 'total' => 200]);
        $estimate->items()->create(['product_name' => 'Item', 'price' => 200, 'qty' => 1, 'total' => 200, 'sort_order' => 0]);

        $this->actingAs($user)->get("/estimates/{$estimate->id}/excel");

        \Maatwebsite\Excel\Facades\Excel::assertDownloaded("estimate-{$estimate->id}.xlsx", function (\App\Exports\EstimateItemsExport $export) {
            return $export->collection()->count() === 1;
        });
    }

    public function test_excel_endpoint_is_forbidden_without_print_permission(): void
    {
        $user = User::factory()->subAdmin(['Estimate', 'Add New Estimate', 'Edit Estimate', 'Delete Estimate'])->create();
        $estimate = Estimate::create(['subject' => 'Excel Estimate', 'bill_date' => now()->toDateString(), 'total' => 200]);

        $this->actingAs($user)->get("/estimates/{$estimate->id}/excel")->assertForbidden();
    }
}
