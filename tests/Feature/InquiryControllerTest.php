<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InquiryControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_inquiry_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/inquiries')->assertForbidden();
    }

    public function test_it_lists_inquiries(): void
    {
        $user = User::factory()->subAdmin(['Inquery'])->create();
        Inquiry::create([
            'fname' => 'Jane', 'lname' => 'Doe', 'email' => 'jane@example.com',
            'subject' => 'Quote request', 'message' => 'Please quote us', 'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/inquiries');

        $response->assertOk();
        $response->assertSee('Jane Doe');
    }

    public function test_a_user_without_delete_access_cannot_delete(): void
    {
        $user = User::factory()->subAdmin(['Inquery'])->create();
        $inquiry = Inquiry::create([
            'fname' => 'Jane', 'lname' => 'Doe', 'email' => 'jane@example.com',
            'subject' => 'Quote request', 'message' => 'Please quote us', 'date' => now()->toDateString(),
        ]);

        $this->actingAs($user)->delete("/inquiries/{$inquiry->id}")->assertForbidden();
        $this->assertDatabaseHas('inquery', ['id' => $inquiry->id]);
    }

    public function test_it_deletes_an_inquiry(): void
    {
        $user = User::factory()->subAdmin(['Inquery', 'Delete Inquery'])->create();
        $inquiry = Inquiry::create([
            'fname' => 'Jane', 'lname' => 'Doe', 'email' => 'jane@example.com',
            'subject' => 'Quote request', 'message' => 'Please quote us', 'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->delete("/inquiries/{$inquiry->id}");

        $response->assertRedirect('/inquiries');
        $this->assertDatabaseMissing('inquery', ['id' => $inquiry->id]);
    }
}
