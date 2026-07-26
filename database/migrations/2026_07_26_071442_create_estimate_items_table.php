<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same shape and same reasoning as bill_items — see that migration and
 * App\Console\Commands\ImportLegacyLineItems.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimate_items', function (Blueprint $table) {
            $table->id();
            // Signed integer, not foreignId: estimate.id is a plain
            // signed `int` (not `int unsigned`, not `bigint`), like the
            // rest of this legacy schema — a foreign key requires an
            // exact type match including signedness.
            $table->integer('estimate_id');
            $table->foreign('estimate_id')->references('id')->on('estimate')->cascadeOnDelete();
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

            $table->index(['estimate_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_items');
    }
};
