<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\SalaryDetail;
use App\Models\SalarySlip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SalarySlipControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeEmployee(): Employee
    {
        $designation = Designation::create(['designation_name' => 'Test Designation '.random_int(10000, 99999)]);

        return Employee::create(['employee_name' => 'Test Employee '.random_int(10000, 99999), 'status' => 1, 'designation_id' => $designation->id]);
    }

    public function test_a_user_without_salary_slip_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/salary-slips')->assertForbidden();
    }

    public function test_it_creates_a_salary_slip_and_mirrors_the_advance_payment_to_the_ledger(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();

        $response = $this->actingAs($user)->post('/salary-slips', [
            'employee_id' => $employee->id,
            'salary_slip_month' => 'January',
            'salary_slip_year' => 2026,
            'day_work' => 26,
            'over_time' => 10,
            'pf_amount' => 500,
            'advance_payment' => 1000,
            'professional_tax' => 200,
        ]);

        $response->assertRedirect('/salary-slips');
        $slip = SalarySlip::where('employee_id', $employee->id)->firstOrFail();

        $ledgerEntry = EmployeeDetail::where('bill_id', $slip->id)->where('type', 'Credit')->first();
        $this->assertNotNull($ledgerEntry);
        $this->assertEquals(1000, $ledgerEntry->amount);
        $this->assertSame('Advance Payment', $ledgerEntry->description);
        $this->assertSame('2026-01-01', $ledgerEntry->date->toDateString());
    }

    public function test_it_does_not_create_a_ledger_entry_when_advance_payment_is_zero(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();

        $this->actingAs($user)->post('/salary-slips', [
            'employee_id' => $employee->id,
            'salary_slip_month' => 'January',
            'salary_slip_year' => 2026,
            'day_work' => 26,
            'over_time' => 0,
            'pf_amount' => 0,
            'advance_payment' => 0,
            'professional_tax' => 0,
        ]);

        $slip = SalarySlip::where('employee_id', $employee->id)->firstOrFail();
        $this->assertDatabaseMissing('employee_details', ['bill_id' => $slip->id]);
    }

    public function test_it_rejects_a_duplicate_slip_for_the_same_employee_month_and_year(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();
        SalarySlip::create([
            'employee_id' => $employee->id, 'salary_slip_month' => 'February', 'salary_slip_year' => 2026,
            'day_work' => 20, 'over_time' => 0, 'pf_amount' => 0, 'advance_payment' => 0, 'professional_tax' => 0,
            'salary_slip_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post('/salary-slips', [
            'employee_id' => $employee->id,
            'salary_slip_month' => 'February',
            'salary_slip_year' => 2026,
            'day_work' => 22,
            'over_time' => 0,
            'pf_amount' => 0,
            'advance_payment' => 0,
            'professional_tax' => 0,
        ]);

        $response->assertSessionHasErrors('employee_id');
        $this->assertSame(1, SalarySlip::where('employee_id', $employee->id)->count());
    }

    public function test_updating_a_slip_keeps_the_ledger_entry_in_sync(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();
        $slip = SalarySlip::create([
            'employee_id' => $employee->id, 'salary_slip_month' => 'March', 'salary_slip_year' => 2026,
            'day_work' => 20, 'over_time' => 0, 'pf_amount' => 0, 'advance_payment' => 500, 'professional_tax' => 0,
            'salary_slip_date' => now()->toDateString(),
        ]);
        EmployeeDetail::create([
            'employee_id' => $employee->id, 'bill_id' => $slip->id, 'type' => 'Credit',
            'amount' => 500, 'description' => 'Advance Payment', 'date' => '2026-03-01',
        ]);

        $this->actingAs($user)->put("/salary-slips/{$slip->id}", [
            'employee_id' => $employee->id,
            'salary_slip_month' => 'March',
            'salary_slip_year' => 2026,
            'day_work' => 20,
            'over_time' => 0,
            'pf_amount' => 0,
            'advance_payment' => 750,
            'professional_tax' => 0,
        ]);

        $ledgerEntry = EmployeeDetail::where('bill_id', $slip->id)->where('type', 'Credit')->first();
        $this->assertEquals(750, $ledgerEntry->amount);
    }

    public function test_updating_a_slip_removes_the_ledger_entry_when_advance_drops_to_zero(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();
        $slip = SalarySlip::create([
            'employee_id' => $employee->id, 'salary_slip_month' => 'April', 'salary_slip_year' => 2026,
            'day_work' => 20, 'over_time' => 0, 'pf_amount' => 0, 'advance_payment' => 500, 'professional_tax' => 0,
            'salary_slip_date' => now()->toDateString(),
        ]);
        EmployeeDetail::create([
            'employee_id' => $employee->id, 'bill_id' => $slip->id, 'type' => 'Credit',
            'amount' => 500, 'description' => 'Advance Payment', 'date' => '2026-04-01',
        ]);

        $this->actingAs($user)->put("/salary-slips/{$slip->id}", [
            'employee_id' => $employee->id,
            'salary_slip_month' => 'April',
            'salary_slip_year' => 2026,
            'day_work' => 20,
            'over_time' => 0,
            'pf_amount' => 0,
            'advance_payment' => 0,
            'professional_tax' => 0,
        ]);

        $this->assertDatabaseMissing('employee_details', ['bill_id' => $slip->id]);
    }

    public function test_deleting_a_slip_also_removes_its_ledger_entry(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();
        $slip = SalarySlip::create([
            'employee_id' => $employee->id, 'salary_slip_month' => 'May', 'salary_slip_year' => 2026,
            'day_work' => 20, 'over_time' => 0, 'pf_amount' => 0, 'advance_payment' => 300, 'professional_tax' => 0,
            'salary_slip_date' => now()->toDateString(),
        ]);
        EmployeeDetail::create([
            'employee_id' => $employee->id, 'bill_id' => $slip->id, 'type' => 'Credit',
            'amount' => 300, 'description' => 'Advance Payment', 'date' => '2026-05-01',
        ]);

        $response = $this->actingAs($user)->delete("/salary-slips/{$slip->id}");

        $response->assertRedirect('/salary-slips');
        $this->assertDatabaseMissing('salary_slip', ['id' => $slip->id]);
        $this->assertDatabaseMissing('employee_details', ['bill_id' => $slip->id]);
    }

    public function test_data_endpoint_returns_attendance_totals_and_rate(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();
        // attendance.attendance/over_time are plain int columns in the
        // legacy schema — no half-days at the DB level.
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-06-05', 'attendance' => 1, 'over_time' => 2]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-06-06', 'attendance' => 1, 'over_time' => 0]);
        SalaryDetail::create(['employee_id' => $employee->id, 'par_day_amount' => 400, 'per_day_extra' => 50, 'date' => '2026-05-01']);

        $response = $this->actingAs($user)->get("/salary-slips/data?employee_id={$employee->id}&month=June&year=2026");

        $response->assertOk();
        $response->assertJson([
            'total_days' => 2,
            'total_over_time' => 2,
            'par_day_amount' => 400,
            'per_day_extra' => 50,
        ]);
    }

    public function test_print_view_computes_earnings_correctly(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();
        SalaryDetail::create(['employee_id' => $employee->id, 'par_day_amount' => 400, 'per_day_extra' => 50, 'date' => '2026-06-01']);
        $slip = SalarySlip::create([
            'employee_id' => $employee->id, 'salary_slip_month' => 'July', 'salary_slip_year' => 2026,
            'day_work' => 25, 'over_time' => 8, 'pf_amount' => 500, 'advance_payment' => 0, 'professional_tax' => 200,
            'salary_slip_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get("/salary-slips/{$slip->id}/print");

        $response->assertOk();
        // basic = round(25*400) + round(8*(400/8)) = 10000 + 400 = 10400
        // extra = round(25*50) + round(8*(50/8)) = 1250 + 50 = 1300
        // total = 11700; deductions = 700; net = 11000
        $response->assertSee('10,400.00', false);
        $response->assertSee('1,300.00', false);
        $response->assertSee('11,700.00', false);
        $response->assertSee('11,000.00', false);
    }

    public function test_pdf_endpoint_downloads_a_pdf(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();
        $slip = SalarySlip::create([
            'employee_id' => $employee->id, 'salary_slip_month' => 'August', 'salary_slip_year' => 2026,
            'day_work' => 20, 'over_time' => 0, 'pf_amount' => 0, 'advance_payment' => 0, 'professional_tax' => 0,
            'salary_slip_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get("/salary-slips/{$slip->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
