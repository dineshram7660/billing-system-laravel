<?php

namespace Tests\Feature;

use App\Mail\EstimateMail;
use App\Models\Estimate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EstimateMailControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_send_email_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();
        $estimate = Estimate::create(['subject' => 'Test', 'bill_date' => now()->toDateString(), 'total' => 100]);

        $this->actingAs($user)->get("/estimates/{$estimate->id}/mail")->assertForbidden();
        $this->actingAs($user)->post("/estimates/{$estimate->id}/mail", [
            'email' => 'client@example.com', 'client_name' => 'ACME',
        ])->assertForbidden();
    }

    public function test_it_sends_the_estimate_mail_and_logs_it(): void
    {
        Mail::fake();

        $user = User::factory()->subAdmin(['Send Email'])->create();
        $estimate = Estimate::create(['subject' => 'Roof Repair', 'bill_date' => now()->toDateString(), 'total' => 100]);
        $estimate->items()->create(['product_name' => 'Item', 'price' => 100, 'qty' => 1, 'total' => 100, 'sort_order' => 0]);

        $response = $this->actingAs($user)->post("/estimates/{$estimate->id}/mail", [
            'email' => 'client@example.com',
            'client_name' => 'ACME Corp',
        ]);

        $response->assertRedirect('/estimates');

        Mail::assertSent(EstimateMail::class, function (EstimateMail $mail) {
            return $mail->hasTo('client@example.com');
        });

        $this->assertDatabaseHas('email_send', [
            'email' => 'client@example.com',
            'client_name' => 'ACME Corp',
            'all_id' => $estimate->id,
            'type' => 'Estimate',
        ]);
    }

    public function test_it_requires_a_valid_email_and_client_name(): void
    {
        $user = User::factory()->subAdmin(['Send Email'])->create();
        $estimate = Estimate::create(['subject' => 'Test', 'bill_date' => now()->toDateString(), 'total' => 100]);

        $response = $this->actingAs($user)->post("/estimates/{$estimate->id}/mail", [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['email', 'client_name']);
    }
}
