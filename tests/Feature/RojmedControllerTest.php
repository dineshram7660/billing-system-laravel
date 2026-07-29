<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RojmedControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_account_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/rojmed?date=2030-01-15')->assertForbidden();
    }

    public function test_it_shows_opening_balance_and_same_day_entries(): void
    {
        $user = User::factory()->subAdmin(['Account'])->create();
        $account = Account::create(['account_name' => 'Cash']);

        $creditBefore = (float) AccountDetail::where('type', 'Credit')->where('date', '<', '2030-01-15')->sum('amount');
        $debitBefore = (float) AccountDetail::where('type', 'Debit')->where('date', '<', '2030-01-15')->sum('amount');

        AccountDetail::create(['account_id' => $account->id, 'type' => 'Credit', 'amount' => 500, 'description' => 'Opening credit', 'date' => '2030-01-10']);
        AccountDetail::create(['account_id' => $account->id, 'type' => 'Debit', 'amount' => 200, 'description' => 'Opening debit', 'date' => '2030-01-12']);
        AccountDetail::create(['account_id' => $account->id, 'type' => 'Debit', 'amount' => 50, 'description' => 'Today debit', 'date' => '2030-01-15']);

        $expectedBalance = abs(($creditBefore + 500) - ($debitBefore + 200));

        $response = $this->actingAs($user)->get('/rojmed?date=2030-01-15');

        $response->assertOk();
        $response->assertSee('Balance b/f');
        $response->assertSee(number_format($expectedBalance, 2));
        $response->assertSee('Today debit');
    }

    public function test_a_user_with_delete_access_can_remove_an_entry(): void
    {
        $user = User::factory()->subAdmin(['Account', 'Delete Account Tranjection'])->create();
        $account = Account::create(['account_name' => 'Bank']);
        $detail = AccountDetail::create(['account_id' => $account->id, 'type' => 'Credit', 'amount' => 100, 'description' => 'Test', 'date' => '2030-02-01']);

        $response = $this->actingAs($user)->delete("/accounts/{$account->id}/details/{$detail->id}");

        $response->assertRedirect(route('accounts.show', $account));
        $this->assertDatabaseMissing('account_details', ['id' => $detail->id]);
    }
}
