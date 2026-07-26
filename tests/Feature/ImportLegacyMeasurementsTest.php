<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\MeasurementBill;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImportLegacyMeasurementsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeBill(): Bill
    {
        return Bill::create([
            'invoice_no' => random_int(900000, 999999),
            'subject' => 'Test bill',
            'gst_no' => '',
            'ref_no' => '',
            'product' => '',
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

    private function makeMeasurement(Bill $bill, string $product): MeasurementBill
    {
        return MeasurementBill::create([
            'b_id' => $bill->id,
            'product' => $product,
        ]);
    }

    public function test_it_imports_a_well_formed_measurement_string(): void
    {
        $bill = $this->makeBill();
        $this->makeMeasurement(
            $bill,
            'SVC1[#][#]100.000[#]hundred[#]TON[#]Desc1[#]5[#]2.5[#]1.2[#]Nos[#]3.000'
        );

        $this->artisan('legacy:import-measurements')->assertSuccessful();

        $items = $bill->measurementItems()->get();

        $this->assertCount(1, $items);
        $this->assertEquals(100.000, $items[0]->total);
        $this->assertSame('hundred', $items[0]->total_text);
        $this->assertSame('TON', $items[0]->total_unit);

        $lines = $items[0]->lines()->get();
        $this->assertCount(1, $lines);
        $this->assertSame('SVC1', $lines[0]->service_no);
        $this->assertSame('Desc1', $lines[0]->description);
        $this->assertSame('5', $lines[0]->no);
        $this->assertSame('2.5', $lines[0]->length);
        $this->assertSame('1.2', $lines[0]->breath);
        $this->assertSame('Nos', $lines[0]->unit);
        $this->assertSame('3.000', $lines[0]->quantity);
    }

    public function test_it_treats_the_empty_placeholder_as_no_items_without_logging_an_issue(): void
    {
        $bill = $this->makeBill();
        $measurement = $this->makeMeasurement($bill, str_repeat('[#]', 10));

        $this->artisan('legacy:import-measurements')->assertSuccessful();

        $this->assertCount(0, $bill->measurementItems()->get());
        $this->assertDatabaseMissing('legacy_import_issues', [
            'source_table' => 'measurement_bill',
            'source_id' => $measurement->id,
        ]);
    }

    public function test_it_logs_a_mismatched_row_instead_of_guessing(): void
    {
        // seg2 has 2 "[@]" groups but seg3 only has 1 — the group counts
        // don't line up, so this must not be silently imported.
        $bill = $this->makeBill();
        $measurement = $this->makeMeasurement(
            $bill,
            'SVC1[@]SVC2[#][#]100[@]200[#]only-one[#]TON[@]TON[#]D1[@]D2'
            .'[#]1[@]1[#]1[@]1[#]1[@]1[#]1[@]1[#]1[@]1'
        );

        $this->artisan('legacy:import-measurements')->assertSuccessful();

        $this->assertCount(0, $bill->measurementItems()->get());
        $this->assertDatabaseHas('legacy_import_issues', [
            'source_table' => 'measurement_bill',
            'source_id' => $measurement->id,
            'reason' => 'Segment/group count mismatch in delimited measurement string',
        ]);
    }

    public function test_it_logs_an_orphaned_row_instead_of_crashing(): void
    {
        $measurement = MeasurementBill::create([
            'b_id' => 999999999,
            'product' => 'SVC1[#][#]100.000[#]hundred[#]TON[#]Desc1[#]5[#]2.5[#]1.2[#]Nos[#]3.000',
        ]);

        $this->artisan('legacy:import-measurements')->assertSuccessful();

        $this->assertDatabaseHas('legacy_import_issues', [
            'source_table' => 'measurement_bill',
            'source_id' => $measurement->id,
            'reason' => 'Orphaned row: no bill with id 999999999',
        ]);
    }

    public function test_it_is_safe_to_run_twice(): void
    {
        $bill = $this->makeBill();
        $this->makeMeasurement(
            $bill,
            'SVC1[#][#]100.000[#]hundred[#]TON[#]Desc1[#]5[#]2.5[#]1.2[#]Nos[#]3.000'
        );

        $this->artisan('legacy:import-measurements')->assertSuccessful();
        $firstRunCount = $bill->measurementItems()->count();

        $this->artisan('legacy:import-measurements')->assertSuccessful();
        $secondRunCount = $bill->measurementItems()->count();

        $this->assertSame(1, $firstRunCount);
        $this->assertSame($firstRunCount, $secondRunCount);
    }
}
