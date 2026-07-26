<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A record of every row the legacy line-item backfill (see
 * App\Console\Commands\ImportLegacyLineItems) couldn't parse cleanly —
 * about 4-5% of bills/estimates have a product string whose "[@]"-separated
 * sub-arrays don't all have the same length, which almost certainly means
 * hand-edited or corrupted data in the original app. Those rows are
 * skipped rather than guessed at; this table is how to find and fix them
 * manually instead of silently getting billing data wrong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_import_issues', function (Blueprint $table) {
            $table->id();
            $table->string('source_table');
            $table->unsignedBigInteger('source_id');
            $table->string('reason');
            $table->longText('raw_value')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['source_table', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_import_issues');
    }
};
