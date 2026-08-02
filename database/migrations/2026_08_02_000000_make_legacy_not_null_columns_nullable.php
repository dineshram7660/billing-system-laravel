<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Several legacy columns are NOT NULL with no default, and either the
 * app's own forms treat them as optional (validated `nullable`) or never
 * populate them at all (dead legacy columns, e.g. employee.username/
 * password, account.username/password/designation_id — see the model
 * docblocks). Both only ever worked because config/database.php disabled
 * MySQL strict mode. This migration gives every one of those columns a
 * real NULL instead, so that workaround can be removed — see the
 * companion change to config/database.php.
 *
 * No data cleanup here (unlike the ref_date/paid_date precedent): existing
 * rows already hold whatever implicit-default value non-strict mode gave
 * them ('' or 0), which stays valid data. Only new inserts need real NULL.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'bill' => [
            'invoice_no' => 'INT',
            'gst_no' => 'TEXT',
            'ref_no' => 'TEXT',
            'd_id' => 'INT',
            'sir_name' => 'TEXT',
            'remark' => 'LONGTEXT',
            'photo' => 'TEXT',
            'address' => 'LONGTEXT',
            'bill_state' => 'TEXT',
            'paid_amount' => 'INT',
            'product' => 'LONGTEXT',
        ],
        'estimate' => [
            'ast_desc' => 'TEXT',
            'address' => 'LONGTEXT',
            'product' => 'LONGTEXT',
        ],
        'quotation' => [
            'quotation_to' => 'TEXT',
            'particulars' => 'LONGTEXT',
            'unit' => 'VARCHAR(200)',
        ],
        'product' => [
            'service_no' => 'TEXT',
            'hsn_code' => 'TEXT',
            'per_unit' => 'TEXT',
        ],
        'employee' => [
            'username' => 'TEXT',
            'password' => 'TEXT',
            'mobile_number' => 'VARCHAR(20)',
            'card_number' => 'TEXT',
            'pf_number' => 'TEXT',
        ],
        'account' => [
            'username' => 'TEXT',
            'password' => 'TEXT',
            'designation_id' => 'INT',
        ],
        'employee_details' => [
            'bill_id' => 'INT',
        ],
        'expenses' => [
            'd_id' => 'INT',
            'description' => 'LONGTEXT',
        ],
        'income' => [
            'd_id' => 'INT',
        ],
    ];

    public function up(): void
    {
        DB::statement("SET sql_mode = ''");

        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column => $type) {
                DB::statement("ALTER TABLE {$table} MODIFY {$column} {$type} NULL");
            }
        }
    }

    public function down(): void
    {
        DB::statement("SET sql_mode = ''");

        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column => $type) {
                $default = str_contains($type, 'INT') ? '0' : "''";
                DB::statement("UPDATE {$table} SET {$column} = {$default} WHERE {$column} IS NULL");
                DB::statement("ALTER TABLE {$table} MODIFY {$column} {$type} NOT NULL");
            }
        }
    }
};
