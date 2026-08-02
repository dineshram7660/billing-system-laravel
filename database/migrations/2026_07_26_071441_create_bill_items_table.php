<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the 8-segment "[#]"/"[@]"-delimited string that used to live in
 * bill.product — see App\Console\Commands\ImportLegacyLineItems, which
 * backfills this table from that column. The column itself is left in
 * place for now as a fallback / audit trail; nothing should read it going
 * forward once the backfill has run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            // Signed integer, not foreignId: bill.id is a plain signed
            // `int` (not `int unsigned`, not `bigint`), like the rest of
            // this legacy schema — a foreign key requires an exact type
            // match including signedness.
            $table->integer('bill_id');
            // Guarded: `bill` doesn't exist on a fresh CI database outside
            // the legacy tables covered so far — see CUTOVER.md's
            // "CI/test-suite gap" section. MySQL rejects a FOREIGN KEY
            // clause referencing a nonexistent table, so this constraint
            // is skipped there rather than breaking the whole migration.
            if (Schema::hasTable('bill')) {
                $table->foreign('bill_id')->references('id')->on('bill')->cascadeOnDelete();
            }
            // Nullable + no FK constraint: the legacy product_id sometimes
            // references a product that's since been deleted, and a few
            // rows have no id at all.
            $table->integer('product_id')->nullable();
            $table->string('service_no')->nullable();
            $table->string('product_name');
            $table->string('hsn_code')->nullable();
            $table->string('per_unit')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('qty', 12, 3)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['bill_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
