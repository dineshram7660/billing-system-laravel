<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Dates use 2030 — the testing DB carries a full copy of real legacy
 * income/expense/bill data, so a near-present date range would pick up
 * real records alongside the test's own.
 */
class DashboardControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_loads_without_a_department_selected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Select a department');
    }

    public function test_it_computes_income_minus_expense_and_billed_work_for_a_department(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_name' => 'Overview Dept']);
        Income::create(['d_id' => $department->id, 'amount' => 1000, 'income_date' => '2030-01-10']);
        Expense::create(['d_id' => $department->id, 'amount' => 200, 'expenses_date' => '2030-01-15', 'description' => 'Supplies']);
        Bill::create(['d_id' => $department->id, 'subject' => 'Test', 'bill_date' => '2030-01-20', 'total' => 300, 'gst_bill' => 0, 'paid' => 0]);

        $response = $this->actingAs($user)->get('/dashboard?'.http_build_query([
            'department_id' => $department->id,
            'from_date' => '2030-01-01',
            'to_date' => '2030-01-31',
        ]));

        $response->assertOk();
        $response->assertSee('1,000.00', false); // income
        $response->assertSee('500.00', false); // expense (200 + 300 billed)
        $response->assertSee('Overview Dept');
    }
}
