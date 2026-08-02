<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Dates use 2030 — the testing DB carries a full copy of real legacy
 * attendance/employee data, so a near-present date range would pick up
 * real records alongside the test's own.
 */
class SalarySheetControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_salary_sheet_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/salary-sheet')->assertForbidden();
        $this->actingAs($user)->get('/salary-sheet/view?start_date=2030-01-01&end_date=2030-01-31')->assertForbidden();
    }

    public function test_it_computes_present_absent_marks_and_pay_total(): void
    {
        $user = User::factory()->create();
        $employee = Employee::create(['employee_name' => 'Sheet Test Employee', 'status' => 1]);
        SalaryDetail::create(['employee_id' => $employee->id, 'par_day_amount' => 400, 'per_day_extra' => 0, 'date' => '2030-01-01']);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2030-01-01', 'attendance' => 1, 'over_time' => 8]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2030-01-02', 'attendance' => 0, 'over_time' => 0]);

        $response = $this->actingAs($user)->get('/salary-sheet/view?start_date=2030-01-01&end_date=2030-01-02');

        $response->assertOk();
        $response->assertSee('Sheet Test Employee');
        // total = (1 day * 400) + (8 overtime hrs * (400/8)) = 400 + 400 = 800.
        $response->assertSee('800.00', false);
    }

    public function test_pdf_endpoint_downloads_a_pdf(): void
    {
        $user = User::factory()->create();
        Employee::create(['employee_name' => 'Sheet Test Employee 2', 'status' => 1]);

        $response = $this->actingAs($user)->get('/salary-sheet/pdf?start_date=2030-01-01&end_date=2030-01-02');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_excel_endpoint_downloads_the_sheet(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->get('/salary-sheet/excel?start_date=2030-01-01&end_date=2030-01-02');

        Excel::assertDownloaded('Bhavani_Engineering_Salary_Sheet.xlsx');
    }
}
