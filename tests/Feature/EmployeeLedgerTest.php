<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmployeeLedgerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeEmployee(): Employee
    {
        return Employee::create(['employee_name' => 'Ledger Test Employee '.random_int(10000, 99999), 'status' => 1]);
    }

    public function test_view_permission_is_distinct_from_list_permission(): void
    {
        $user = User::factory()->subAdmin(['Employee'])->create();
        $employee = $this->makeEmployee();

        $this->actingAs($user)->get('/employees')->assertOk();
        $this->actingAs($user)->get("/employees/{$employee->id}")->assertForbidden();
    }

    public function test_it_adds_and_lists_ledger_transactions_with_a_running_balance(): void
    {
        $user = User::factory()->subAdmin(['Employee', 'View Employee', 'Credit Debit Employee'])->create();
        $employee = $this->makeEmployee();

        $this->actingAs($user)->post("/employees/{$employee->id}/details", [
            'date' => now()->toDateString(), 'description' => 'Advance', 'type' => 'Debit', 'amount' => 800,
        ]);
        $this->actingAs($user)->post("/employees/{$employee->id}/details", [
            'date' => now()->toDateString(), 'description' => 'Repayment', 'type' => 'Credit', 'amount' => 300,
        ]);

        $response = $this->actingAs($user)->get("/employees/{$employee->id}");

        $response->assertOk();
        $response->assertSee('500.00', false);
        $response->assertSee('Advance');
        $response->assertSee('Repayment');
    }

    public function test_deleting_a_ledger_transaction_requires_its_own_permission(): void
    {
        $user = User::factory()->subAdmin(['Employee', 'View Employee', 'Credit Debit Employee'])->create();
        $employee = $this->makeEmployee();
        $detail = $employee->details()->create(['date' => now()->toDateString(), 'description' => 'Test', 'type' => 'Debit', 'amount' => 100]);

        $this->actingAs($user)->delete("/employees/{$employee->id}/details/{$detail->id}")->assertForbidden();

        $userWithDelete = User::factory()->subAdmin(['Employee', 'View Employee', 'Delete Employee Tranjection'])->create();
        $response = $this->actingAs($userWithDelete)->delete("/employees/{$employee->id}/details/{$detail->id}");

        $response->assertRedirect("/employees/{$employee->id}");
        $this->assertDatabaseMissing('employee_details', ['id' => $detail->id]);
    }
}
