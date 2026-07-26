<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IncomeControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_income_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/incomes')->assertForbidden();
    }

    public function test_it_creates_an_income(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_name' => 'Test Dept']);

        $response = $this->actingAs($user)->post('/incomes', [
            'd_id' => $department->id,
            'amount' => 25000,
            'income_date' => now()->toDateString(),
        ]);

        $response->assertRedirect('/incomes');
        $this->assertDatabaseHas('income', ['amount' => 25000, 'd_id' => $department->id]);
    }

    public function test_it_updates_and_deletes_an_income(): void
    {
        $user = User::factory()->create();
        $income = Income::create(['amount' => 100, 'income_date' => now()->toDateString()]);

        $update = $this->actingAs($user)->put("/incomes/{$income->id}", [
            'amount' => 500, 'income_date' => now()->toDateString(),
        ]);
        $update->assertRedirect('/incomes');
        $this->assertDatabaseHas('income', ['id' => $income->id, 'amount' => 500]);

        $delete = $this->actingAs($user)->delete("/incomes/{$income->id}");
        $delete->assertRedirect('/incomes');
        $this->assertDatabaseMissing('income', ['id' => $income->id]);
    }
}
