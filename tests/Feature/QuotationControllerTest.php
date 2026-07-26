<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuotationControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_without_quotation_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Department'])->create();

        $this->actingAs($user)->get('/quotations')->assertForbidden();
    }

    public function test_it_creates_a_quotation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/quotations', [
            'quotation_to' => "ACME Corp\nAhmedabad",
            'subject' => 'Test quotation',
            'particulars' => 'Fabrication work',
            'unit' => 'Lump Sum',
            'total' => 15000,
            'bill_date' => now()->toDateString(),
        ]);

        $response->assertRedirect('/quotations');
        $this->assertDatabaseHas('quotation', ['subject' => 'Test quotation', 'total' => 15000]);
    }

    public function test_it_requires_a_subject_and_total(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/quotations', [
            'bill_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['subject', 'total']);
    }

    public function test_updating_a_quotation(): void
    {
        $user = User::factory()->create();
        $quotation = Quotation::create([
            'subject' => 'Original', 'particulars' => 'Old', 'unit' => 'Nos',
            'total' => 100, 'bill_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->put("/quotations/{$quotation->id}", [
            'subject' => 'Updated',
            'particulars' => 'New',
            'unit' => 'Nos',
            'total' => 200,
            'bill_date' => now()->toDateString(),
        ]);

        $response->assertRedirect('/quotations');
        $quotation->refresh();
        $this->assertSame('Updated', $quotation->subject);
        $this->assertEquals(200, $quotation->total);
    }

    public function test_deleting_a_quotation(): void
    {
        $user = User::factory()->create();
        $quotation = Quotation::create(['subject' => 'To delete', 'total' => 100, 'bill_date' => now()->toDateString()]);

        $response = $this->actingAs($user)->delete("/quotations/{$quotation->id}");

        $response->assertRedirect('/quotations');
        $this->assertDatabaseMissing('quotation', ['id' => $quotation->id]);
    }

    public function test_print_view_shows_the_quotation(): void
    {
        $user = User::factory()->create();
        $quotation = Quotation::create([
            'quotation_to' => 'ACME Corp', 'subject' => 'Printable quotation',
            'particulars' => 'Fabrication', 'unit' => 'Nos', 'total' => 500, 'bill_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get("/quotations/{$quotation->id}/print");

        $response->assertOk();
        $response->assertSee('Printable quotation');
        $response->assertSee('ACME Corp');
    }
}
