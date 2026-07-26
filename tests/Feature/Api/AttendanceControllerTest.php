<?php

namespace Tests\Feature\Api;

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
        return Employee::create(['employee_name' => 'Api Test Employee '.random_int(10000, 99999), 'status' => 1, 'employee' => 1]);
    }

    private function actingToken(): string
    {
        $user = User::factory()->create();

        return $user->createToken('test')->plainTextToken;
    }

    public function test_it_returns_eligible_employees_with_null_attendance_for_untouched_days(): void
    {
        $token = $this->actingToken();
        $employee = $this->makeEmployee();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/attendance?date=2030-04-01');

        $response->assertOk();
        $data = collect($response->json('data.attendance'));
        $entry = $data->firstWhere('employee_id', $employee->id);

        $this->assertNotNull($entry);
        $this->assertNull($entry['attendance']);
        $this->assertNull($entry['over_time']);
    }

    public function test_it_returns_existing_attendance_for_the_requested_date(): void
    {
        $token = $this->actingToken();
        $employee = $this->makeEmployee();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2030-04-02', 'attendance' => 1, 'over_time' => 4]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/attendance?date=2030-04-02');

        $response->assertOk();
        $entry = collect($response->json('data.attendance'))->firstWhere('employee_id', $employee->id);
        $this->assertSame(1, $entry['attendance']);
        $this->assertSame(4, $entry['over_time']);
    }

    public function test_it_saves_attendance_and_is_idempotent(): void
    {
        $token = $this->actingToken();
        $employee = $this->makeEmployee();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/attendance', [
                'employee_id' => $employee->id, 'date' => '2030-04-03', 'attendance' => 1, 'over_time' => 2,
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/attendance', [
                'employee_id' => $employee->id, 'date' => '2030-04-03', 'attendance' => 0, 'over_time' => 0,
            ])
            ->assertOk();

        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->where('date', '2030-04-03')->count());
        $this->assertDatabaseHas('attendance', [
            'employee_id' => $employee->id, 'date' => '2030-04-03', 'attendance' => 0, 'over_time' => 0,
        ]);
    }

    public function test_it_validates_the_save_payload(): void
    {
        $token = $this->actingToken();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/attendance', ['date' => '2030-04-04'])
            ->assertStatus(422);
    }
}
