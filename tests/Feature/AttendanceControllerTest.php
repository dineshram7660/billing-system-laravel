<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeEmployee(): Employee
    {
        return Employee::create(['employee_name' => 'Test Employee '.random_int(10000, 99999), 'status' => 1, 'employee' => 1]);
    }

    public function test_a_user_without_attendance_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/attendance')->assertForbidden();
    }

    public function test_it_lists_attendance_for_existing_employees_only(): void
    {
        $user = User::factory()->subAdmin(['Attendance'])->create();
        $employee = $this->makeEmployee();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2030-01-05', 'attendance' => 1, 'over_time' => 2]);

        // The testing DB carries a full copy of real legacy attendance
        // data (see README), so an unfiltered index would put this row
        // on some later page — scope by the search filter instead.
        $response = $this->actingAs($user)->get('/attendance?search='.urlencode($employee->employee_name));

        $response->assertOk();
        $response->assertSee($employee->employee_name);
    }

    public function test_bulk_daily_add_upserts_attendance_for_all_eligible_employees(): void
    {
        $user = User::factory()->subAdmin(['Attendance', 'Add Attendance'])->create();
        $employeeA = $this->makeEmployee();
        $employeeB = $this->makeEmployee();

        $response = $this->actingAs($user)->post('/attendance/create', [
            'date' => '2030-02-10',
            'attendance' => [$employeeA->id => 1],
            'over_time' => [$employeeA->id => 3, $employeeB->id => 0],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance', [
            'employee_id' => $employeeA->id, 'date' => '2030-02-10', 'attendance' => 1, 'over_time' => 3,
        ]);
        $this->assertDatabaseHas('attendance', [
            'employee_id' => $employeeB->id, 'date' => '2030-02-10', 'attendance' => 0, 'over_time' => 0,
        ]);
    }

    public function test_bulk_daily_add_is_idempotent_for_the_same_date(): void
    {
        $user = User::factory()->subAdmin(['Attendance', 'Add Attendance'])->create();
        $employee = $this->makeEmployee();

        $this->actingAs($user)->post('/attendance/create', [
            'date' => '2030-02-11', 'attendance' => [$employee->id => 1], 'over_time' => [$employee->id => 1],
        ]);
        $this->actingAs($user)->post('/attendance/create', [
            'date' => '2030-02-11', 'attendance' => [$employee->id => 0], 'over_time' => [$employee->id => 5],
        ]);

        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->where('date', '2030-02-11')->count());
        $this->assertDatabaseHas('attendance', [
            'employee_id' => $employee->id, 'date' => '2030-02-11', 'attendance' => 0, 'over_time' => 5,
        ]);
    }

    public function test_month_grid_saves_attendance_for_every_day_in_the_month(): void
    {
        $user = User::factory()->subAdmin(['Attendance', 'Add Attendance'])->create();
        $employee = $this->makeEmployee();

        $response = $this->actingAs($user)->post('/attendance/month', [
            'year' => 2030,
            'month' => 2,
            'attendance' => [$employee->id => ['2030-02-01' => 1, '2030-02-15' => 1]],
            'over_time' => [$employee->id => ['2030-02-01' => 2]],
        ]);

        $response->assertRedirect();
        $this->assertSame(28, Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', ['2030-02-01', '2030-02-28'])->count());
        $this->assertDatabaseHas('attendance', ['employee_id' => $employee->id, 'date' => '2030-02-01', 'attendance' => 1, 'over_time' => 2]);
        $this->assertDatabaseHas('attendance', ['employee_id' => $employee->id, 'date' => '2030-02-15', 'attendance' => 1, 'over_time' => 0]);
        $this->assertDatabaseHas('attendance', ['employee_id' => $employee->id, 'date' => '2030-02-02', 'attendance' => 0, 'over_time' => 0]);
    }

    public function test_it_updates_and_deletes_a_single_record(): void
    {
        $user = User::factory()->subAdmin(['Attendance', 'Edit Attendance', 'Delete Attendance'])->create();
        $employee = $this->makeEmployee();
        $attendance = Attendance::create(['employee_id' => $employee->id, 'date' => '2030-03-01', 'attendance' => 0, 'over_time' => 0]);

        $update = $this->actingAs($user)->put("/attendance/{$attendance->id}", [
            'date' => '2030-03-01', 'attendance' => 1, 'over_time' => 4,
        ]);
        $update->assertRedirect('/attendance');
        $this->assertDatabaseHas('attendance', ['id' => $attendance->id, 'attendance' => 1, 'over_time' => 4]);

        $delete = $this->actingAs($user)->delete("/attendance/{$attendance->id}");
        $delete->assertRedirect('/attendance');
        $this->assertDatabaseMissing('attendance', ['id' => $attendance->id]);
    }
}
