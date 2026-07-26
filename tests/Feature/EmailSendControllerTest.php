<?php

namespace Tests\Feature;

use App\Models\EmailSend;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmailSendControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_send_email_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/email-sends')->assertForbidden();
    }

    public function test_it_lists_sent_emails(): void
    {
        $user = User::factory()->subAdmin(['Send Email'])->create();
        EmailSend::create([
            'client_name' => 'ACME Corp', 'email' => 'client@example.com',
            'file_name' => 'estimate-1.pdf', 'measurement' => '', 'all_id' => 1,
            'date' => now()->toDateString(), 'type' => 'Estimate',
        ]);

        $response = $this->actingAs($user)->get('/email-sends');

        $response->assertOk();
        $response->assertSee('ACME Corp');
    }
}
