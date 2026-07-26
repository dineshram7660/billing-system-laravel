<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same shape and reasoning as measurement_bill_item_lines, for
 * measurement_estimate_items instead. See that migration and
 * App\Console\Commands\ImportLegacyMeasurements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_estimate_item_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('measurement_estimate_item_id')
                ->constrained('measurement_estimate_items', 'id', 'measurement_estimate_item_lines_item_id_foreign')
                ->cascadeOnDelete();
            $table->string('service_no')->nullable();
            $table->string('description')->nullable();
            $table->string('no')->nullable();
            $table->string('length')->nullable();
            $table->string('breath')->nullable();
            $table->string('unit')->nullable();
            $table->string('quantity')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['measurement_estimate_item_id', 'sort_order'], 'measurement_estimate_item_lines_item_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_estimate_item_lines');
    }
};
