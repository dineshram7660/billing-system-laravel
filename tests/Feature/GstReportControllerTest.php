<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Dates in these tests use 2030 rather than "now" — the testing DB
 * carries a full copy of the real legacy bill data (see README), so a
 * near-present date range would pick up real bills alongside the test's
 * own, throwing off the total assertions.
 */
class GstReportControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_gst_report_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/gst-report')->assertForbidden();
        $this->actingAs($user)->get('/gst-report/view?start_date=2030-01-01&end_date=2030-01-31')->assertForbidden();
    }

    public function test_it_computes_gst_tds_and_grand_totals_for_a_gst_bill(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_name' => 'Test Dept']);

        $bill = Bill::create([
            'invoice_no' => 555001, 'subject' => 'GST bill', 'bill_date' => '2030-01-15',
            'd_id' => $department->id, 'total' => 1000, 'gst_bill' => 1, 'paid' => 1, 'paid_amount' => 500,
        ]);

        $response = $this->actingAs($user)->get('/gst-report/view?start_date=2030-01-01&end_date=2030-01-31');

        $response->assertOk();
        // 1000 + 90 CGST + 90 SGST = 1180; TDS = 10; grand = 1170.
        $response->assertSee('1,180.00', false);
        $response->assertSee('1,170.00', false);
        $response->assertSee((string) $bill->invoice_no);
    }

    public function test_it_skips_gst_for_a_non_gst_bill(): void
    {
        $user = User::factory()->create();

        Bill::create([
            'invoice_no' => 555002, 'subject' => 'Non-GST bill', 'bill_date' => '2030-01-15',
            'total' => 1000, 'gst_bill' => 0, 'paid' => 0,
        ]);

        $response = $this->actingAs($user)->get('/gst-report/view?start_date=2030-01-01&end_date=2030-01-31');

        $response->assertOk();
        // No CGST/SGST: total amount = 1000, TDS = 10, grand = 990.
        $response->assertSee('990.00', false);
    }

    public function test_it_excludes_bills_outside_the_date_range(): void
    {
        $user = User::factory()->create();

        Bill::create([
            'invoice_no' => 555003, 'subject' => 'Out of range', 'bill_date' => '2030-03-15',
            'total' => 1000, 'gst_bill' => 1, 'paid' => 0,
        ]);

        $response = $this->actingAs($user)->get('/gst-report/view?start_date=2030-01-01&end_date=2030-01-31');

        $response->assertOk();
        $response->assertSee('No bills in this date range.');
    }

    public function test_pdf_endpoint_downloads_a_pdf(): void
    {
        $user = User::factory()->create();

        Bill::create([
            'invoice_no' => 555004, 'subject' => 'PDF bill', 'bill_date' => '2030-01-15',
            'total' => 1000, 'gst_bill' => 1, 'paid' => 0,
        ]);

        $response = $this->actingAs($user)->get('/gst-report/pdf?start_date=2030-01-01&end_date=2030-01-31');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_pdf_endpoint_is_forbidden_without_access(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/gst-report/pdf?start_date=2030-01-01&end_date=2030-01-31')->assertForbidden();
    }
}
