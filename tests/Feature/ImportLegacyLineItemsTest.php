<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\LegacyImportIssue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImportLegacyLineItemsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeBill(string $product): Bill
    {
        return Bill::create([
            'invoice_no' => random_int(900000, 999999),
            'subject' => 'Test bill',
            'gst_no' => '',
            'ref_no' => '',
            'product' => $product,
            'total' => 0,
            'bill_date' => now()->toDateString(),
            'paid' => 0,
            'paid_amount' => 0,
            'd_id' => 0,
            'sir_name' => '',
            'remark' => '',
            'photo' => '',
            'address' => '',
            'bill_state' => '',
            'gst_bill' => 0,
        ]);
    }

    public function test_it_imports_a_well_formed_product_string(): void
    {
        $bill = $this->makeBill(
            '10[@]20[#]SVC1[@]SVC2[#]First item[@]Second item[#]HSN1[@]HSN2'
            .'[#]NO[@]NO[#]100.00[@]50.00[#]2[@]4[#]200.00[@]200.00'
        );

        $this->artisan('legacy:import-line-items')->assertSuccessful();

        $items = $bill->items()->get();

        $this->assertCount(2, $items);
        $this->assertSame('SVC1', $items[0]->service_no);
        $this->assertSame('First item', $items[0]->product_name);
        $this->assertEquals(100.00, $items[0]->price);
        $this->assertEquals(2, $items[0]->qty);
        $this->assertEquals(200.00, $items[0]->total);
        $this->assertSame('SVC2', $items[1]->service_no);
    }

    public function test_it_treats_the_empty_placeholder_as_no_items_without_logging_an_issue(): void
    {
        $bill = $this->makeBill('[#][#][#][#][#][#][#]');

        $this->artisan('legacy:import-line-items')->assertSuccessful();

        $this->assertCount(0, $bill->items()->get());
        $this->assertDatabaseMissing('legacy_import_issues', [
            'source_table' => 'bill',
            'source_id' => $bill->id,
        ]);
    }

    public function test_it_logs_a_mismatched_row_instead_of_guessing(): void
    {
        // 2 service numbers but only 1 product name — the "[@]" arrays
        // don't line up, so this must not be silently imported.
        $bill = $this->makeBill(
            'SVC1[@]SVC2[#]Only one name[#]HSN1[#]NO[#]100.00[#]2[#]200.00'
        );

        $this->artisan('legacy:import-line-items')->assertSuccessful();

        $this->assertCount(0, $bill->items()->get());
        $this->assertDatabaseHas('legacy_import_issues', [
            'source_table' => 'bill',
            'source_id' => $bill->id,
            'reason' => 'Segment count mismatch in delimited product string',
        ]);
    }

    public function test_it_is_safe_to_run_twice(): void
    {
        $bill = $this->makeBill('10[#]SVC1[#]An item[#]HSN1[#]NO[#]100.00[#]1[#]100.00');

        $this->artisan('legacy:import-line-items')->assertSuccessful();
        $firstRunCount = $bill->items()->count();

        $this->artisan('legacy:import-line-items')->assertSuccessful();
        $secondRunCount = $bill->items()->count();

        $this->assertSame(1, $firstRunCount);
        $this->assertSame($firstRunCount, $secondRunCount);
    }
}
