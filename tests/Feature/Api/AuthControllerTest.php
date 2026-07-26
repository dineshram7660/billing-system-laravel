<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_logs_in_and_issues_a_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.user_id', $user->id);
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_it_upgrades_a_legacy_md5_password_on_login(): void
    {
        $user = User::factory()->create();
        // Bypass the model's 'hashed' cast (which would bcrypt this
        // value on assignment) to store a literal legacy MD5 hash, like
        // a real un-migrated admin account has.
        DB::table('admin')->where('id', $user->id)->update(['password' => md5('legacy-pass')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'legacy-pass',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::isHashed($user->fresh()->password));
    }

    public function test_it_rejects_an_invalid_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_a_valid_token_can_call_a_protected_endpoint_and_logout(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);
        $login = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password123']);
        $token = $login->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/attendance?date='.now()->toDateString())
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('status', 'success');

        // The auth guard caches its resolved user for the lifetime of
        // the (single, shared-across-these-calls) test application
        // instance — force it to re-resolve so this next call actually
        // re-checks the token against the DB, like a real separate HTTP
        // request would.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/attendance?date='.now()->toDateString())
            ->assertStatus(401);
    }

    public function test_unauthenticated_requests_to_protected_endpoints_are_rejected(): void
    {
        $this->getJson('/api/attendance?date='.now()->toDateString())->assertStatus(401);
        $this->postJson('/api/attendance', [])->assertStatus(401);
    }
}
