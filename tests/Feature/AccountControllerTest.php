<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_account_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/accounts')->assertForbidden();
    }

    public function test_it_creates_an_account(): void
    {
        $user = User::factory()->subAdmin(['Account', 'Add New Account'])->create();

        $response = $this->actingAs($user)->post('/accounts', ['account_name' => 'Cash']);

        $response->assertRedirect('/accounts');
        $this->assertDatabaseHas('account', ['account_name' => 'Cash']);
    }

    public function test_it_rejects_a_duplicate_account_name(): void
    {
        $user = User::factory()->subAdmin(['Account', 'Add New Account'])->create();
        Account::create(['account_name' => 'Cash']);

        $response = $this->actingAs($user)->post('/accounts', ['account_name' => 'Cash']);

        $response->assertSessionHasErrors('account_name');
    }

    public function test_view_permission_is_distinct_from_list_permission(): void
    {
        $user = User::factory()->subAdmin(['Account'])->create();
        $account = Account::create(['account_name' => 'Cash']);

        $this->actingAs($user)->get('/accounts')->assertOk();
        $this->actingAs($user)->get("/accounts/{$account->id}")->assertForbidden();
    }

    public function test_it_adds_and_lists_ledger_transactions_with_a_running_balance(): void
    {
        $user = User::factory()->subAdmin(['Account', 'View Account', 'Credit Debit Account'])->create();
        $account = Account::create(['account_name' => 'Cash']);

        $this->actingAs($user)->post("/accounts/{$account->id}/details", [
            'date' => now()->toDateString(), 'description' => 'Opening balance', 'type' => 'Debit', 'amount' => 500,
        ]);
        $this->actingAs($user)->post("/accounts/{$account->id}/details", [
            'date' => now()->toDateString(), 'description' => 'Payment received', 'type' => 'Credit', 'amount' => 200,
        ]);

        $response = $this->actingAs($user)->get("/accounts/{$account->id}");

        $response->assertOk();
        $response->assertSee('300.00', false);
        $response->assertSee('Opening balance');
        $response->assertSee('Payment received');
    }

    public function test_deleting_a_ledger_transaction_requires_its_own_permission(): void
    {
        $user = User::factory()->subAdmin(['Account', 'View Account', 'Credit Debit Account'])->create();
        $account = Account::create(['account_name' => 'Cash']);
        $detail = $account->details()->create(['date' => now()->toDateString(), 'description' => 'Test', 'type' => 'Debit', 'amount' => 100]);

        $this->actingAs($user)->delete("/accounts/{$account->id}/details/{$detail->id}")->assertForbidden();

        $userWithDelete = User::factory()->subAdmin(['Account', 'View Account', 'Delete Account Tranjection'])->create();
        $response = $this->actingAs($userWithDelete)->delete("/accounts/{$account->id}/details/{$detail->id}");

        $response->assertRedirect("/accounts/{$account->id}");
        $this->assertDatabaseMissing('account_details', ['id' => $detail->id]);
    }
}
