<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Estimate;
use App\Models\LegacyImportIssue;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Backfills bill_items / estimate_items from the legacy "[#]"/"[@]"
 * -delimited bill.product / estimate.product strings — see the migration
 * roadmap's Phase 4 and the comments on Bill::items() / Estimate::items().
 *
 * Safe to re-run: each source row's items are replaced (delete-then-insert)
 * rather than appended, and legacy_import_issues is likewise reset per
 * source before re-checking it. Nothing here touches the original
 * bill.product / estimate.product columns.
 */
#[Signature('legacy:import-line-items {--fresh : Truncate bill_items, estimate_items and legacy_import_issues first}')]
#[Description('Backfill bill_items/estimate_items from the legacy delimited product column')]
class ImportLegacyLineItems extends Command
{
    /**
     * The 8 "[@]"-separated segments bill.product / estimate.product are
     * split into by "[#]", in order — see add_edit_bill.php /
     * add_edit_estimate.php in the legacy codebase.
     */
    private const array FIELDS = [
        'product_id', 'service_no', 'product_name', 'hsn_code',
        'per_unit', 'price', 'qty', 'total',
    ];

    public function handle(): int
    {
        if ($this->option('fresh')) {
            DB::table('bill_items')->truncate();
            DB::table('estimate_items')->truncate();
            DB::table('legacy_import_issues')->truncate();
        }

        $this->components->info('Importing bill line items…');
        $billStats = $this->importFrom(Bill::query(), 'bill', 'bill_id', 'bill_items');

        $this->components->info('Importing estimate line items…');
        $estimateStats = $this->importFrom(Estimate::query(), 'estimate', 'estimate_id', 'estimate_items');

        $this->newLine();
        $this->components->twoColumnDetail('Bills', "{$billStats['imported']} imported, {$billStats['empty']} empty, {$billStats['skipped']} skipped");
        $this->components->twoColumnDetail('Estimates', "{$estimateStats['imported']} imported, {$estimateStats['empty']} empty, {$estimateStats['skipped']} skipped");

        if ($billStats['skipped'] + $estimateStats['skipped'] > 0) {
            $this->newLine();
            $this->components->warn('Some rows could not be parsed — see the legacy_import_issues table for what needs manual review.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{imported: int, empty: int, skipped: int}
     */
    private function importFrom(
        Builder $query,
        string $sourceTable,
        string $foreignKey,
        string $itemsTable,
    ): array {
        $stats = ['imported' => 0, 'empty' => 0, 'skipped' => 0];

        $query->orderBy('id')->chunkById(200, function ($rows) use (&$stats, $sourceTable, $foreignKey, $itemsTable) {
            foreach ($rows as $row) {
                DB::table($itemsTable)->where($foreignKey, $row->id)->delete();
                LegacyImportIssue::where('source_table', $sourceTable)->where('source_id', $row->id)->delete();

                $parsed = $this->parse($row->product);

                if ($parsed === null) {
                    $stats['empty']++;

                    continue;
                }

                if ($parsed === false) {
                    LegacyImportIssue::create([
                        'source_table' => $sourceTable,
                        'source_id' => $row->id,
                        'reason' => 'Segment count mismatch in delimited product string',
                        'raw_value' => $row->product,
                    ]);
                    $stats['skipped']++;

                    continue;
                }

                $now = now();
                $itemRows = [];

                foreach ($parsed as $sortOrder => $item) {
                    $itemRows[] = [
                        $foreignKey => $row->id,
                        'product_id' => $item['product_id'] !== '' ? (int) $item['product_id'] : null,
                        'service_no' => $item['service_no'] !== '' ? $item['service_no'] : null,
                        'product_name' => $item['product_name'],
                        'hsn_code' => $item['hsn_code'] !== '' ? $item['hsn_code'] : null,
                        'per_unit' => $item['per_unit'] !== '' ? $item['per_unit'] : null,
                        'price' => is_numeric($item['price']) ? $item['price'] : 0,
                        'qty' => is_numeric($item['qty']) ? $item['qty'] : 0,
                        'total' => is_numeric($item['total']) ? $item['total'] : 0,
                        'sort_order' => $sortOrder,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table($itemsTable)->insert($itemRows);
                $stats['imported']++;
            }
        });

        return $stats;
    }

    /**
     * @return array<int, array<string, string>>|null|false
     *
     * null: nothing to import (blank/placeholder product); false: malformed,
     * needs manual review; array: one row per line item, in order.
     */
    private function parse(?string $product): array|null|false
    {
        $product = trim((string) $product);

        if ($product === '' || $product === str_repeat('[#]', 7)) {
            return null;
        }

        $segments = explode('[#]', $product);

        if (count($segments) !== count(self::FIELDS)) {
            return false;
        }

        $columns = [];
        $rowCount = null;

        foreach ($segments as $i => $segment) {
            $values = explode('[@]', $segment);

            if ($rowCount === null) {
                $rowCount = count($values);
            } elseif (count($values) !== $rowCount) {
                return false;
            }

            $columns[self::FIELDS[$i]] = $values;
        }

        $items = [];

        for ($row = 0; $row < $rowCount; $row++) {
            $item = [];

            foreach (self::FIELDS as $field) {
                $item[$field] = trim($columns[$field][$row]);
            }

            // A row with no product name at all isn't a line item — it's
            // what an all-empty "[@]" slot (from a trailing separator, or
            // a row the legacy UI let someone half-delete) looks like.
            if ($item['product_name'] === '' && $item['service_no'] === '') {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }
}
