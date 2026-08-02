<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\LegacyImportIssue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportLegacyBillPhotosTest extends TestCase
{
    use DatabaseTransactions;

    private string $sourceDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceDir = sys_get_temp_dir().'/legacy-bill-photos-test-'.uniqid();
        mkdir($this->sourceDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->sourceDir.'/*') as $file) {
            unlink($file);
        }
        rmdir($this->sourceDir);

        parent::tearDown();
    }

    private function makeBill(string $photo): Bill
    {
        return Bill::create([
            'invoice_no' => random_int(900000, 999999),
            'subject' => 'Test bill',
            'total' => 0,
            'bill_date' => now()->toDateString(),
            'paid' => 0,
            'photo' => $photo,
            'gst_bill' => 0,
        ]);
    }

    public function test_it_copies_a_legacy_photo_and_rewrites_the_bill_column(): void
    {
        Storage::fake('public');
        file_put_contents($this->sourceDir.'/existing.jpg', 'fake-image-bytes');
        $bill = $this->makeBill('existing.jpg');

        Artisan::call('legacy:import-bill-photos', ['--source' => $this->sourceDir]);

        Storage::disk('public')->assertExists('bill-photos/existing.jpg');
        $this->assertSame('bill-photos/existing.jpg', $bill->fresh()->photo);
    }

    public function test_it_logs_a_missing_file_and_drops_it_from_the_bill(): void
    {
        Storage::fake('public');
        $bill = $this->makeBill('missing.jpg,also-missing.jpg');

        Artisan::call('legacy:import-bill-photos', ['--source' => $this->sourceDir]);

        $this->assertSame('', $bill->fresh()->photo);
        $this->assertSame(2, LegacyImportIssue::where('source_table', 'bill_photo')->where('source_id', $bill->id)->count());
    }

    public function test_it_leaves_an_already_migrated_bill_untouched(): void
    {
        Storage::fake('public');
        $bill = $this->makeBill('bill-photos/already-there.jpg');

        Artisan::call('legacy:import-bill-photos', ['--source' => $this->sourceDir]);

        Storage::disk('public')->assertMissing('bill-photos/already-there.jpg');
        $this->assertSame('bill-photos/already-there.jpg', $bill->fresh()->photo);
    }

    public function test_it_fails_gracefully_when_the_source_directory_does_not_exist(): void
    {
        $exitCode = Artisan::call('legacy:import-bill-photos', ['--source' => '/nonexistent/path']);

        $this->assertSame(1, $exitCode);
    }
}
