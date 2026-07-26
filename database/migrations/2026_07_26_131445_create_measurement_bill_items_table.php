<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces measurement_bill.product — an 11-segment "[#]"-delimited
 * string, one "[@]"-separated value per product group for the fields
 * here (total/total_text/total_unit); the per-line detail within each
 * product group goes in measurement_bill_item_lines. See
 * App\Console\Commands\ImportLegacyMeasurements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_bill_items', function (Blueprint $table) {
            $table->id();
            // Signed integer, not foreignId: bill.id is a plain signed
            // `int`, not `bigint` — see bill_items for the same note.
            $table->integer('bill_id');
            $table->foreign('bill_id')->references('id')->on('bill')->cascadeOnDelete();
            // Left as strings rather than decimal: this is legacy display
            // data (a formatted running total per product group) and its
            // cleanliness hasn't been audited as closely as bill_items'
            // price/qty/total — don't assume it's always parseable as a
            // number without checking first.
            $table->string('total')->nullable();
            $table->string('total_text')->nullable();
            $table->string('total_unit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['bill_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_bill_items');
    }
};
