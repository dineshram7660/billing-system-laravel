<?php

namespace App\Console\Commands;

use App\Models\LegacyImportIssue;
use App\Models\MeasurementBill;
use App\Models\MeasurementEstimate;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Backfills measurement_bill_items(+lines) / measurement_estimate_items
 * (+lines) from the legacy 11-segment "[#]"/"[@]"/"[(@)]"-delimited
 * measurement_bill.product / measurement_estimate.product strings.
 *
 * The format nests two levels: "[#]" splits 11 fields; within each field,
 * "[@]" splits one value per *product group* (segments 0, 2, 3, 4, 5, 6,
 * 7, 8, 9, 10 — segment 1 is always empty, the legacy form concatenates
 * an undefined $service_desc variable there); within a product group's
 * slot in fields 0, 5, 6, 7, 8, 9, 10, "[(@)]" further splits one value
 * per *measurement line* within that product (multiple length x breadth
 * entries per product). Fields 2/3/4 (total/total_text/total_unit) are
 * one value per product group, not per line.
 *
 * Same safety approach as ImportLegacyLineItems: malformed rows are
 * logged to legacy_import_issues and skipped, never guessed at. Safe to
 * re-run (delete-then-insert per source row).
 */
#[Signature('legacy:import-measurements {--fresh : Truncate the measurement item/line tables and legacy_import_issues rows for these sources first}')]
#[Description('Backfill measurement_bill_items/measurement_estimate_items (+lines) from the legacy delimited product column')]
class ImportLegacyMeasurements extends Command
{
    /** Segment index => field name, for the one-value-per-product-group fields. */
    private const array ITEM_FIELDS = [2 => 'total', 3 => 'total_text', 4 => 'total_unit'];

    /** Segment index => field name, for the one-value-per-line fields. */
    private const array LINE_FIELDS = [
        0 => 'service_no', 5 => 'description', 6 => 'no',
        7 => 'length', 8 => 'breath', 9 => 'unit', 10 => 'quantity',
    ];

    private const int SEGMENT_COUNT = 11;

    public function handle(): int
    {
        if ($this->option('fresh')) {
            // TRUNCATE refuses to run on a table another table's FK still
            // points at, even once that child table is empty — disable
            // the check for this pair of statements rather than reordering.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('measurement_bill_item_lines')->truncate();
            DB::table('measurement_bill_items')->truncate();
            DB::table('measurement_estimate_item_lines')->truncate();
            DB::table('measurement_estimate_items')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            LegacyImportIssue::whereIn('source_table', ['measurement_bill', 'measurement_estimate'])->delete();
        }

        $this->components->info('Importing bill measurements…');
        $billStats = $this->importFrom(
            MeasurementBill::query(), 'measurement_bill', 'b_id', 'bill_id',
            'measurement_bill_items', 'measurement_bill_item_lines', 'measurement_bill_item_id', 'bill',
        );

        $this->components->info('Importing estimate measurements…');
        $estimateStats = $this->importFrom(
            MeasurementEstimate::query(), 'measurement_estimate', 'e_id', 'estimate_id',
            'measurement_estimate_items', 'measurement_estimate_item_lines', 'measurement_estimate_item_id', 'estimate',
        );

        $this->newLine();
        $this->components->twoColumnDetail('Bill measurements', "{$billStats['imported']} imported, {$billStats['empty']} empty, {$billStats['skipped']} skipped");
        $this->components->twoColumnDetail('Estimate measurements', "{$estimateStats['imported']} imported, {$estimateStats['empty']} empty, {$estimateStats['skipped']} skipped");

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
        string $sourceForeignKey,
        string $targetForeignKey,
        string $itemsTable,
        string $linesTable,
        string $lineForeignKey,
        string $parentTable,
    ): array {
        $stats = ['imported' => 0, 'empty' => 0, 'skipped' => 0];
        $validParentIds = DB::table($parentTable)->pluck('id')->flip();

        $query->orderBy('id')->chunkById(200, function ($rows) use (&$stats, $sourceTable, $sourceForeignKey, $targetForeignKey, $itemsTable, $linesTable, $lineForeignKey, $validParentIds, $parentTable) {
            foreach ($rows as $row) {
                $sourceId = $row->{$sourceForeignKey}; // b_id or e_id — the bill/estimate this measurement belongs to

                if (! isset($validParentIds[$sourceId])) {
                    // Orphaned measurement row — its bill/estimate was
                    // deleted but this row wasn't. Nothing to attach it to.
                    LegacyImportIssue::create([
                        'source_table' => $sourceTable,
                        'source_id' => $row->id,
                        'reason' => "Orphaned row: no {$parentTable} with id {$sourceId}",
                        'raw_value' => $row->product,
                    ]);
                    $stats['skipped']++;

                    continue;
                }

                $existingItemIds = DB::table($itemsTable)->where($targetForeignKey, $sourceId)->pluck('id');
                DB::table($linesTable)->whereIn($lineForeignKey, $existingItemIds)->delete();
                DB::table($itemsTable)->where($targetForeignKey, $sourceId)->delete();
                LegacyImportIssue::where('source_table', $sourceTable)->where('source_id', $row->id)->delete();

                $groups = $this->parse($row->product);

                if ($groups === null) {
                    $stats['empty']++;

                    continue;
                }

                if ($groups === false) {
                    LegacyImportIssue::create([
                        'source_table' => $sourceTable,
                        'source_id' => $row->id,
                        'reason' => 'Segment/group count mismatch in delimited measurement string',
                        'raw_value' => $row->product,
                    ]);
                    $stats['skipped']++;

                    continue;
                }

                $now = now();

                foreach ($groups as $groupOrder => $group) {
                    $itemId = DB::table($itemsTable)->insertGetId([
                        $targetForeignKey => $sourceId,
                        'total' => $group['total'] !== '' ? $group['total'] : null,
                        'total_text' => $group['total_text'] !== '' ? $group['total_text'] : null,
                        'total_unit' => $group['total_unit'] !== '' ? $group['total_unit'] : null,
                        'sort_order' => $groupOrder,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    if ($group['lines'] === []) {
                        continue;
                    }

                    $lineRows = [];

                    foreach ($group['lines'] as $lineOrder => $line) {
                        $lineRows[] = [
                            $lineForeignKey => $itemId,
                            'service_no' => $line['service_no'] !== '' ? $line['service_no'] : null,
                            'description' => $line['description'] !== '' ? $line['description'] : null,
                            'no' => $line['no'] !== '' ? $line['no'] : null,
                            'length' => $line['length'] !== '' ? $line['length'] : null,
                            'breath' => $line['breath'] !== '' ? $line['breath'] : null,
                            'unit' => $line['unit'] !== '' ? $line['unit'] : null,
                            'quantity' => $line['quantity'] !== '' ? $line['quantity'] : null,
                            'sort_order' => $lineOrder,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    DB::table($linesTable)->insert($lineRows);
                }

                $stats['imported']++;
            }
        });

        return $stats;
    }

    /**
     * @return array<int, array{total: string, total_text: string, total_unit: string, lines: array<int, array<string, string>>}>|null|false
     *
     * null: nothing to import; false: malformed, needs manual review;
     * array: one entry per product group, each carrying its measurement lines.
     */
    private function parse(?string $product): array|null|false
    {
        $product = trim((string) $product);

        if ($product === '' || $product === str_repeat('[#]', self::SEGMENT_COUNT - 1)) {
            return null;
        }

        $segments = explode('[#]', $product);

        if (count($segments) !== self::SEGMENT_COUNT) {
            return false;
        }

        // Segment 1 is always empty — the legacy code concatenates an
        // undefined $service_desc variable there. Nothing to parse.

        $itemValues = [];
        $lineValuesByField = [];
        $groupCount = null;

        foreach (self::ITEM_FIELDS as $index => $field) {
            $values = explode('[@]', $segments[$index]);

            if ($groupCount === null) {
                $groupCount = count($values);
            } elseif (count($values) !== $groupCount) {
                return false;
            }

            $itemValues[$field] = $values;
        }

        foreach (self::LINE_FIELDS as $index => $field) {
            $values = explode('[@]', $segments[$index]);

            if (count($values) !== $groupCount) {
                return false;
            }

            $lineValuesByField[$field] = $values;
        }

        $groups = [];

        for ($g = 0; $g < $groupCount; $g++) {
            $lineCount = null;
            $linesByField = [];

            foreach ($lineValuesByField as $field => $perGroupValues) {
                $lines = explode('[(@)]', $perGroupValues[$g]);

                if ($lineCount === null) {
                    $lineCount = count($lines);
                } elseif (count($lines) !== $lineCount) {
                    return false;
                }

                $linesByField[$field] = $lines;
            }

            $lines = [];

            for ($l = 0; $l < $lineCount; $l++) {
                $line = [];

                foreach ($linesByField as $field => $values) {
                    $line[$field] = trim($values[$l]);
                }

                if (implode('', $line) === '') {
                    continue; // every field blank — not a real line
                }

                $lines[] = $line;
            }

            $groups[] = [
                'total' => trim($itemValues['total'][$g]),
                'total_text' => trim($itemValues['total_text'][$g]),
                'total_unit' => trim($itemValues['total_unit'][$g]),
                'lines' => $lines,
            ];
        }

        return $groups;
    }
}
