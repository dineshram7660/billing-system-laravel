<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BillPhotoControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeBill(): Bill
    {
        return Bill::create(['subject' => 'Photo test bill', 'bill_date' => now()->toDateString(), 'total' => 0, 'gst_bill' => 0, 'paid' => 0]);
    }

    public function test_a_user_without_edit_bill_access_is_forbidden(): void
    {
        $user = User::factory()->subAdmin(['Bill'])->create();
        $bill = $this->makeBill();

        $this->actingAs($user)->get("/bills/{$bill->id}/photos")->assertForbidden();
    }

    public function test_it_uploads_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $bill = $this->makeBill();

        $response = $this->actingAs($user)->put("/bills/{$bill->id}/photos", [
            'photos' => [UploadedFile::fake()->image('site.jpg')],
        ]);

        $response->assertRedirect(route('bills.photos.edit', $bill));
        $bill->refresh();
        $this->assertNotEmpty($bill->photo);
        Storage::disk('public')->assertExists(explode(',', $bill->photo)[0]);
    }

    public function test_it_keeps_only_checked_photos_and_deletes_the_rest(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $bill = $this->makeBill();

        // Simulate two already-uploaded photos.
        $keep = UploadedFile::fake()->image('keep.jpg')->store('bill-photos', 'public');
        $remove = UploadedFile::fake()->image('remove.jpg')->store('bill-photos', 'public');
        $bill->update(['photo' => "{$keep},{$remove}"]);

        $response = $this->actingAs($user)->put("/bills/{$bill->id}/photos", [
            'keep' => [$keep],
        ]);

        $response->assertRedirect(route('bills.photos.edit', $bill));
        $bill->refresh();
        $this->assertSame($keep, $bill->photo);
        Storage::disk('public')->assertExists($keep);
        Storage::disk('public')->assertMissing($remove);
    }

    public function test_it_rejects_a_non_image_upload(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $bill = $this->makeBill();

        $response = $this->actingAs($user)->put("/bills/{$bill->id}/photos", [
            'photos' => [UploadedFile::fake()->create('doc.pdf', 10)],
        ]);

        $response->assertSessionHasErrors('photos.0');
    }
}
