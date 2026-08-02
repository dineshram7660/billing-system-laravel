<?php

/**
 * Paths on the legacy app's own filesystem that this app needs to read
 * from during cutover — never written to. Only meaningful on whatever
 * machine actually has a copy of the legacy app checked out.
 */
return [
    // The legacy admin panel's photo upload directory (UPLOAD_URL in
    // admin/includes/config.php, physically admin/image/) — source for
    // App\Console\Commands\ImportLegacyBillPhotos.
    'bill_photos_path' => env('LEGACY_BILL_PHOTOS_PATH'),
];
