<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\SalaryDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SalaryDetailControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeEmployee(): Employee
    {
        return Employee::create(['employee_name' => 'Test Employee '.random_int(10000, 99999), 'status' => 1]);
    }

    public function test_a_user_without_view_salary_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();
        $employee = $this->makeEmployee();

        $this->actingAs($user)->get("/employees/{$employee->id}/salary-details")->assertForbidden();
    }

    public function test_it_adds_a_pay_rate_for_an_employee(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();

        $response = $this->actingAs($user)->post("/employees/{$employee->id}/salary-details", [
            'par_day_amount' => 450,
            'per_day_extra' => 60,
            'date' => '2026-01-01',
        ]);

        $response->assertRedirect(route('employees.salary-details.index', $employee));
        $this->assertDatabaseHas('salary_details', [
            'employee_id' => $employee->id, 'par_day_amount' => 450, 'per_day_extra' => 60,
        ]);
    }

    public function test_it_deletes_a_pay_rate(): void
    {
        $user = User::factory()->create();
        $employee = $this->makeEmployee();
        $rate = SalaryDetail::create(['employee_id' => $employee->id, 'par_day_amount' => 400, 'per_day_extra' => 50, 'date' => '2026-01-01']);

        $response = $this->actingAs($user)->delete("/employees/{$employee->id}/salary-details/{$rate->id}");

        $response->assertRedirect(route('employees.salary-details.index', $employee));
        $this->assertDatabaseMissing('salary_details', ['id' => $rate->id]);
    }
}
