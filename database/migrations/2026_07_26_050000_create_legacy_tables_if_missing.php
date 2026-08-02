<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * On every real environment this app runs against, these tables already
 * exist — they're part of the one-time legacy DB dump import (see
 * phpunit.xml's comment), not something Laravel ever created. This
 * migration is a no-op there.
 *
 * It exists for the one environment that *doesn't* have that dump: a
 * fresh CI database. Each Schema::create is guarded so it only fires
 * against a genuinely bare database, giving the rest of the migration
 * suite (and the Employee-module feature tests) something to run
 * against without needing a copy of real business data.
 *
 * Tables are created in their *original* legacy shape, not today's
 * already-fixed-up shape — e.g. employee.username/password/
 * mobile_number/card_number/pf_number and employee_details.bill_id go in
 * NOT NULL with no default, exactly as the dump had them before
 * 2026_08_02_000000_make_legacy_not_null_columns_nullable touched them,
 * and admin has none of the Laravel auth columns
 * 2026_07_26_055915_add_laravel_auth_columns_to_admin_table adds. Both
 * of those migrations then run completely unmodified on top and produce
 * the exact same end state they do on real environments — no special
 * casing for "did this slice just create the table."
 *
 * Only the 8 tables the Employee module's feature tests touch are
 * covered so far (admin is a hard prerequisite for every test via
 * App\Models\User, not Employee-specific) — see CUTOVER.md's
 * "CI/test-suite gap" section for the remaining 13 legacy tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin')) {
            Schema::create('admin', function (Blueprint $table) {
                $table->id();
                $table->text('first_name');
                $table->text('last_name');
                $table->text('email');
                $table->text('password');
                $table->integer('type')->default(1);
                $table->longText('access');
            });
        }

        if (! Schema::hasTable('department')) {
            Schema::create('department', function (Blueprint $table) {
                $table->id();
                $table->string('department_name');
            });
        }

        if (! Schema::hasTable('designation')) {
            Schema::create('designation', function (Blueprint $table) {
                $table->id();
                $table->text('designation_name');
            });
        }

        if (! Schema::hasTable('employee')) {
            Schema::create('employee', function (Blueprint $table) {
                $table->id();
                $table->text('employee_name');
                $table->text('username');
                $table->text('password');
                $table->integer('status')->default(1);
                $table->integer('employee')->default(1);
                $table->integer('par_day')->default(0);
                $table->integer('designation_id');
                $table->text('card_number');
                $table->string('mobile_number', 20);
                $table->text('pf_number');
            });
        }

        if (! Schema::hasTable('employee_details')) {
            Schema::create('employee_details', function (Blueprint $table) {
                $table->id();
                $table->integer('employee_id');
                $table->integer('bill_id');
                $table->string('type', 200);
                $table->float('amount');
                $table->text('description');
                $table->date('date');
            });
        }

        if (! Schema::hasTable('attendance')) {
            Schema::create('attendance', function (Blueprint $table) {
                $table->id();
                $table->integer('employee_id');
                $table->date('date');
                $table->integer('attendance')->default(0);
                $table->integer('over_time')->default(0);
            });
        }

        if (! Schema::hasTable('salary_details')) {
            Schema::create('salary_details', function (Blueprint $table) {
                $table->id();
                $table->integer('employee_id');
                $table->float('par_day_amount');
                $table->integer('per_day_extra')->default(0);
                $table->date('date');
            });
        }

        if (! Schema::hasTable('salary_slip')) {
            Schema::create('salary_slip', function (Blueprint $table) {
                $table->id();
                $table->integer('employee_id')->default(0);
                $table->integer('day_work')->default(0);
                $table->integer('par_day')->default(0);
                $table->integer('over_time')->default(0);
                $table->integer('pf_amount')->default(0);
                $table->integer('extra_pay')->default(0);
                $table->date('salary_slip_date');
                $table->string('salary_slip_month', 100);
                $table->string('salary_slip_year', 100);
                $table->integer('advance_payment')->default(0);
                $table->integer('advance_payment_earnings')->default(0);
                $table->integer('total_advance_payment')->default(0);
                $table->integer('professional_tax')->default(0);
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty: on real environments these tables predate
        // this migration and must not be dropped by rolling it back; on a
        // fresh CI database rolling back is only ever done by tearing
        // down the whole ephemeral database, not by running `down()`.
    }
};
