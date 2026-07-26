<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same shape and reasoning as measurement_bill_items, for
 * measurement_estimate.product instead. See that migration and
 * App\Console\Commands\ImportLegacyMeasurements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_estimate_items', function (Blueprint $table) {
            $table->id();
            // Signed integer, not foreignId: estimate.id is a plain
            // signed `int`, not `bigint` — see estimate_items.
            $table->integer('estimate_id');
            $table->foreign('estimate_id')->references('id')->on('estimate')->cascadeOnDelete();
            $table->string('total')->nullable();
            $table->string('total_text')->nullable();
            $table->string('total_unit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['estimate_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_estimate_items');
    }
};
