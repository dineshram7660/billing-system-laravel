<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_expense_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/expenses')->assertForbidden();
    }

    public function test_it_creates_an_expense(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_name' => 'Test Dept']);

        $response = $this->actingAs($user)->post('/expenses', [
            'd_id' => $department->id,
            'amount' => 1500,
            'expenses_date' => now()->toDateString(),
            'description' => 'Office supplies',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', ['amount' => 1500, 'description' => 'Office supplies']);
    }

    public function test_it_updates_and_deletes_an_expense(): void
    {
        $user = User::factory()->create();
        $expense = Expense::create(['amount' => 100, 'expenses_date' => now()->toDateString(), 'description' => 'Old']);

        $update = $this->actingAs($user)->put("/expenses/{$expense->id}", [
            'amount' => 200, 'expenses_date' => now()->toDateString(), 'description' => 'Updated',
        ]);
        $update->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 200, 'description' => 'Updated']);

        $delete = $this->actingAs($user)->delete("/expenses/{$expense->id}");
        $delete->assertRedirect('/expenses');
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
