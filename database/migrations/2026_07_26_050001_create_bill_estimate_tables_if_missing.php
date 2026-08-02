<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same reasoning as 2026_07_26_050000_create_legacy_tables_if_missing —
 * a no-op on every real environment (these tables come from the legacy
 * DB dump import, not Laravel), only actually creating schema on a
 * fresh CI database. This covers the Bill/Estimate module: bill,
 * estimate, quotation, product, bill_estimate, measurement_bill,
 * measurement_estimate — the last two are the raw legacy blob tables
 * MeasurementBill/MeasurementEstimate map to, distinct from the
 * already-migrated measurement_bill_items/measurement_estimate_items.
 *
 * Tables are created in their *original* legacy shape — e.g.
 * bill.invoice_no/gst_no/ref_no/d_id/sir_name/remark/photo/address/
 * bill_state/paid_amount/product, estimate.ast_desc/address/product,
 * quotation.quotation_to/particulars/unit, and
 * product.service_no/hsn_code/per_unit all go in NOT NULL with no
 * default — their current nullable state is
 * 2026_08_02_000000_make_legacy_not_null_columns_nullable's doing, and
 * bill.ref_date/paid_date go in NOT NULL — nullable is
 * 2026_07_26_061221_make_bill_dates_nullable_and_clean_zero_dates's
 * doing. Both of those migrations (already guarded with
 * Schema::hasTable() from the Employee-module slice) then run
 * unmodified on top and reach the same end state as real environments.
 * Likewise, create_bill_items_table / create_estimate_items_table /
 * create_measurement_bill_items_table /
 * create_measurement_estimate_items_table's guarded FK constraints
 * against bill/estimate will actually attach now that those tables
 * exist here, same as production.
 *
 * Also includes email_send: not part of the original Bill/Estimate
 * scoping, but EstimateMailControllerTest exercises
 * EstimateMailController's write into it as a side effect of sending an
 * estimate — a hidden dependency a test-file-import grep alone couldn't
 * catch, found by actually running the tests against a bare database.
 *
 * See CUTOVER.md's "CI/test-suite gap" section for the remaining legacy
 * tables still without a schema migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bill')) {
            Schema::create('bill', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->primary('id');
                $table->integer('invoice_no');
                $table->text('subject');
                $table->text('gst_no');
                $table->text('ref_no');
                $table->date('ref_date');
                $table->longText('product');
                $table->decimal('total', 10, 2);
                $table->date('bill_date');
                $table->integer('paid')->default(0);
                $table->integer('paid_amount');
                $table->date('paid_date');
                $table->integer('d_id');
                $table->text('sir_name');
                $table->longText('remark');
                $table->text('photo');
                $table->longText('address');
                $table->text('bill_state');
                $table->integer('gst_bill')->default(1);
            });
        }

        if (! Schema::hasTable('estimate')) {
            Schema::create('estimate', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->primary('id');
                $table->text('subject');
                $table->text('ast_desc');
                $table->longText('product');
                $table->decimal('total', 10, 2);
                $table->date('bill_date');
                $table->longText('address');
            });
        }

        if (! Schema::hasTable('quotation')) {
            Schema::create('quotation', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->primary('id');
                $table->text('quotation_to');
                $table->text('subject');
                $table->longText('particulars');
                $table->string('unit', 200);
                $table->decimal('total', 10, 2);
                $table->date('bill_date');
            });
        }

        if (! Schema::hasTable('product')) {
            Schema::create('product', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->primary('id');
                $table->string('product_name');
                $table->decimal('price', 10, 2);
                $table->text('service_no');
                $table->text('hsn_code');
                $table->text('per_unit');
            });
        }

        if (! Schema::hasTable('bill_estimate')) {
            Schema::create('bill_estimate', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->primary('id');
                $table->integer('e_id');
                $table->longText('product');
            });
        }

        if (! Schema::hasTable('measurement_bill')) {
            Schema::create('measurement_bill', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->primary('id');
                $table->integer('b_id');
                $table->longText('product');
            });
        }

        if (! Schema::hasTable('measurement_estimate')) {
            Schema::create('measurement_estimate', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->primary('id');
                $table->integer('e_id');
                $table->longText('product');
            });
        }

        if (! Schema::hasTable('email_send')) {
            Schema::create('email_send', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->primary('id');
                $table->text('client_name');
                $table->text('email');
                $table->text('file_name');
                $table->date('date');
                $table->text('measurement');
                $table->integer('all_id');
                $table->text('type');
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty — see
        // 2026_07_26_050000_create_legacy_tables_if_missing's down().
    }
};
