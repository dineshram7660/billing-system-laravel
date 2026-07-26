<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MeasurementBillControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeBill(): Bill
    {
        return Bill::create(['subject' => 'Measurement test bill', 'bill_date' => now()->toDateString(), 'total' => 0, 'gst_bill' => 0, 'paid' => 0]);
    }

    public function test_a_user_without_edit_measurement_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Bill'])->create();
        $bill = $this->makeBill();

        $this->actingAs($user)->get("/bills/{$bill->id}/measurement")->assertForbidden();
    }

    public function test_it_saves_groups_and_lines_replacing_prior_data(): void
    {
        $user = User::factory()->subAdmin(['Edit Measurement'])->create();
        $bill = $this->makeBill();

        $response = $this->actingAs($user)->put("/bills/{$bill->id}/measurement", [
            'groups' => [
                [
                    'total' => '10.5', 'total_text' => 'ten point five', 'total_unit' => 'MTR',
                    'lines' => [
                        ['service_no' => 'SVC1', 'description' => 'Cable run', 'no' => '2', 'length' => '3', 'breath' => '1', 'unit' => '1', 'quantity' => '6.000'],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('bills.measurement.edit', $bill));
        $bill->refresh();
        $this->assertCount(1, $bill->measurementItems);
        $this->assertEquals(10.5, $bill->measurementItems->first()->total);
        $this->assertCount(1, $bill->measurementItems->first()->lines);
        $this->assertSame('Cable run', $bill->measurementItems->first()->lines->first()->description);

        // Saving again with different data replaces, not appends.
        $this->actingAs($user)->put("/bills/{$bill->id}/measurement", [
            'groups' => [
                ['total' => '5', 'total_text' => '', 'total_unit' => '', 'lines' => [
                    ['service_no' => 'SVC2', 'description' => 'Panel', 'no' => '', 'length' => '', 'breath' => '', 'unit' => '', 'quantity' => ''],
                ]],
            ],
        ]);

        $bill->refresh();
        $this->assertCount(1, $bill->measurementItems);
        $this->assertSame('Panel', $bill->measurementItems->first()->lines->first()->description);
    }

    public function test_print_and_pdf_require_a_distinct_permission(): void
    {
        $user = User::factory()->subAdmin(['Edit Measurement'])->create();
        $bill = $this->makeBill();

        $this->actingAs($user)->get("/bills/{$bill->id}/measurement/print")->assertForbidden();

        $printUser = User::factory()->subAdmin(['Print Bill Measurement'])->create();
        $response = $this->actingAs($printUser)->get("/bills/{$bill->id}/measurement/print");
        $response->assertOk();
        $response->assertSee('Measurement Sheet');
    }

    public function test_pdf_endpoint_downloads_a_pdf(): void
    {
        $user = User::factory()->subAdmin(['Print Bill Measurement'])->create();
        $bill = $this->makeBill();
        $item = $bill->measurementItems()->create(['total' => 1, 'sort_order' => 0]);
        $item->lines()->create(['service_no' => 'SVC1', 'description' => 'Test', 'sort_order' => 0]);

        $response = $this->actingAs($user)->get("/bills/{$bill->id}/measurement/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
