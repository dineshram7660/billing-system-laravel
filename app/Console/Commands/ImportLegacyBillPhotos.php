<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\LegacyImportIssue;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Copies bill.photo uploads out of the legacy admin panel's upload
 * directory (UPLOAD_URL in admin/includes/config.php, physically
 * admin/image/) into this app's storage/app/public/bill-photos, and
 * rewrites bill.photo from bare legacy filenames to the disk-relative
 * paths App\Http\Controllers\BillPhotoController expects
 * ("bill-photos/xyz.jpg"). Without this, pre-existing legacy photos
 * 404 — see BillPhotoController's docblock, which this command resolves.
 *
 * Safe to re-run: a filename already prefixed "bill-photos/" (already
 * migrated, or a new upload made through this app) is left untouched;
 * one already copied into storage is re-linked without copying again.
 * Never touches the source directory or the legacy database it reads
 * from.
 */
#[Signature('legacy:import-bill-photos {--source= : Path to the legacy admin/image directory; defaults to LEGACY_BILL_PHOTOS_PATH in .env}')]
#[Description('Copy legacy bill.photo uploads into storage and rewrite bill.photo to the new paths')]
class ImportLegacyBillPhotos extends Command
{
    public function handle(): int
    {
        $source = rtrim($this->option('source') ?: (string) config('legacy.bill_photos_path'), '/');

        if ($source === '' || ! is_dir($source)) {
            $this->components->error("Legacy photo directory not found: [{$source}]. Pass --source=/path/to/admin/image or set LEGACY_BILL_PHOTOS_PATH in .env.");

            return self::FAILURE;
        }

        DB::table('legacy_import_issues')->where('source_table', 'bill_photo')->delete();

        $bills = Bill::query()->whereNotNull('photo')->where('photo', '!=', '')->get(['id', 'photo']);

        $copied = 0;
        $relinked = 0;
        $missing = 0;
        $billsUpdated = 0;

        foreach ($bills as $bill) {
            $filenames = array_filter(array_map('trim', explode(',', (string) $bill->photo)));
            $reconciled = [];
            $changed = false;

            foreach ($filenames as $filename) {
                if (str_starts_with($filename, 'bill-photos/')) {
                    $reconciled[] = $filename;

                    continue;
                }

                $storagePath = 'bill-photos/'.$filename;
                $changed = true;

                if (Storage::disk('public')->exists($storagePath)) {
                    $reconciled[] = $storagePath;
                    $relinked++;

                    continue;
                }

                $sourcePath = $source.'/'.$filename;

                if (! is_file($sourcePath)) {
                    LegacyImportIssue::create([
                        'source_table' => 'bill_photo',
                        'source_id' => $bill->id,
                        'reason' => 'File not found in source directory',
                        'raw_value' => $filename,
                    ]);
                    $missing++;

                    continue;
                }

                Storage::disk('public')->put($storagePath, file_get_contents($sourcePath));
                $reconciled[] = $storagePath;
                $copied++;
            }

            if ($changed) {
                $bill->update(['photo' => implode(',', $reconciled)]);
                $billsUpdated++;
            }
        }

        $this->components->twoColumnDetail('Bills updated', (string) $billsUpdated);
        $this->components->twoColumnDetail('Files copied', (string) $copied);
        $this->components->twoColumnDetail('Files already present', (string) $relinked);
        $this->components->twoColumnDetail('Files missing from source', (string) $missing);

        if ($missing > 0) {
            $this->newLine();
            $this->components->warn('Some referenced photos were not found in the source directory — see the legacy_import_issues table.');
        }

        return self::SUCCESS;
    }
}
