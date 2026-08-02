<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `bill.ref_date` and `bill.paid_date` are NOT NULL with no default, and
 * carry '0000-00-00' for "not yet paid" / "no reference date" (171 and 152
 * rows respectively at time of writing) — a pre-Laravel convention that
 * only worked because the old app ran under a lenient MySQL sql_mode.
 * Modern strict mode rejects that literal outright, and Carbon silently
 * mangles it into a garbage date rather than erroring, so it needs to
 * become a real NULL instead of being carried forward as a string sentinel.
 *
 * Guarded on `bill` existing: on a database that doesn't have the legacy
 * dump's tables (a fresh CI database outside the slice of legacy tables
 * that's been given a schema migration so far — see
 * 2026_07_26_050000_create_legacy_tables_if_missing.php and CUTOVER.md's
 * "CI/test-suite gap" section), `bill` doesn't exist yet, so there's
 * nothing to clean up.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bill')) {
            return;
        }

        DB::statement("SET sql_mode = ''");
        // The columns have to accept NULL *before* the cleanup UPDATE, or
        // MySQL silently coerces the NULL assignment back to the zero-date
        // default instead of erroring — same as it does for the legacy app.
        DB::statement('ALTER TABLE bill MODIFY ref_date DATE NULL');
        DB::statement('ALTER TABLE bill MODIFY paid_date DATE NULL');
        DB::statement("UPDATE bill SET ref_date = NULL WHERE ref_date = '0000-00-00'");
        DB::statement("UPDATE bill SET paid_date = NULL WHERE paid_date = '0000-00-00'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('bill')) {
            return;
        }

        DB::statement("SET sql_mode = ''");
        DB::statement("UPDATE bill SET ref_date = '0000-00-00' WHERE ref_date IS NULL");
        DB::statement("UPDATE bill SET paid_date = '0000-00-00' WHERE paid_date IS NULL");
        DB::statement('ALTER TABLE bill MODIFY ref_date DATE NOT NULL');
        DB::statement('ALTER TABLE bill MODIFY paid_date DATE NOT NULL');
    }
};
