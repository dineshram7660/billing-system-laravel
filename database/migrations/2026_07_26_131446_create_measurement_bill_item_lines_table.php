<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per individual measurement line (a length × breadth × quantity
 * entry) within a measurement_bill_items product group — see that
 * migration and App\Console\Commands\ImportLegacyMeasurements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_bill_item_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('measurement_bill_item_id')
                ->constrained('measurement_bill_items', 'id', 'measurement_bill_item_lines_item_id_foreign')
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

            $table->index(['measurement_bill_item_id', 'sort_order'], 'measurement_bill_item_lines_item_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_bill_item_lines');
    }
};
