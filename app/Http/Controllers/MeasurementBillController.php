<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Estimate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Rebuilds add_edit_bill_measurement.php/bill_measurement_print.php.
 * Operates directly on the already-normalized measurement_bill_items/
 * measurement_bill_item_lines tables (populated by
 * App\Console\Commands\ImportLegacyMeasurements) rather than
 * round-tripping through the legacy delimited `measurement_bill.product`
 * blob — new features build against the normalized tables, matching the
 * pattern established for bill_items/estimate_items.
 */
class MeasurementBillController extends Controller
{
    public function edit(Bill $bill): View
    {
        Gate::authorize('edit-measurement');

        $bill->load('measurementItems.lines');

        $copyableEstimates = Estimate::query()
            ->whereHas('measurementItems')
            ->orderBy('subject')
            ->get(['id', 'subject']);

        return view('bills.measurement', [
            'bill' => $bill,
            'groups' => $this->groupsForForm($bill),
            'copyableEstimates' => $copyableEstimates,
        ]);
    }

    /**
     * Rebuilds the "Copy Measurement" convenience from
     * add_edit_bill_measurement.php: appends another Estimate's
     * measurement sheet onto this Bill's as additional groups, rather
     * than replacing what's already there.
     */
    public function copyFromEstimate(Request $request, Bill $bill): RedirectResponse
    {
        Gate::authorize('edit-measurement');

        $validated = $request->validate([
            'estimate_id' => ['required', 'integer', 'exists:estimate,id'],
        ]);

        $estimate = Estimate::with('measurementItems.lines')->findOrFail($validated['estimate_id']);

        DB::transaction(function () use ($bill, $estimate) {
            $nextOrder = ((int) $bill->measurementItems()->max('sort_order')) + 1;

            foreach ($estimate->measurementItems as $offset => $sourceItem) {
                $item = $bill->measurementItems()->create([
                    'total' => $sourceItem->total,
                    'total_text' => $sourceItem->total_text,
                    'total_unit' => $sourceItem->total_unit,
                    'sort_order' => $nextOrder + $offset,
                ]);

                foreach ($sourceItem->lines as $lineOrder => $sourceLine) {
                    $item->lines()->create([
                        'service_no' => $sourceLine->service_no,
                        'description' => $sourceLine->description,
                        'no' => $sourceLine->no,
                        'length' => $sourceLine->length,
                        'breath' => $sourceLine->breath,
                        'unit' => $sourceLine->unit,
                        'quantity' => $sourceLine->quantity,
                        'sort_order' => $lineOrder,
                    ]);
                }
            }
        });

        return redirect()->route('bills.measurement.edit', $bill)->with('status', 'Measurement sheet copied successfully.');
    }

    public function update(Request $request, Bill $bill): RedirectResponse
    {
        Gate::authorize('edit-measurement');

        $validated = $request->validate([
            'groups' => ['required', 'array', 'min:1'],
            'groups.*.total' => ['nullable', 'numeric'],
            'groups.*.total_text' => ['nullable', 'string', 'max:255'],
            'groups.*.total_unit' => ['nullable', 'string', 'max:255'],
            'groups.*.lines' => ['required', 'array', 'min:1'],
            'groups.*.lines.*.service_no' => ['nullable', 'string', 'max:255'],
            'groups.*.lines.*.description' => ['nullable', 'string', 'max:255'],
            'groups.*.lines.*.no' => ['nullable', 'string', 'max:255'],
            'groups.*.lines.*.length' => ['nullable', 'string', 'max:255'],
            'groups.*.lines.*.breath' => ['nullable', 'string', 'max:255'],
            'groups.*.lines.*.unit' => ['nullable', 'string', 'max:255'],
            'groups.*.lines.*.quantity' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($bill, $validated) {
            $existingItemIds = $bill->measurementItems()->pluck('id');
            DB::table('measurement_bill_item_lines')->whereIn('measurement_bill_item_id', $existingItemIds)->delete();
            $bill->measurementItems()->delete();

            foreach (array_values($validated['groups']) as $groupOrder => $group) {
                $item = $bill->measurementItems()->create([
                    'total' => $group['total'] !== '' ? $group['total'] : null,
                    'total_text' => $group['total_text'] ?? null,
                    'total_unit' => $group['total_unit'] ?? null,
                    'sort_order' => $groupOrder,
                ]);

                foreach (array_values($group['lines']) as $lineOrder => $line) {
                    $item->lines()->create([...$line, 'sort_order' => $lineOrder]);
                }
            }
        });

        return redirect()->route('bills.measurement.edit', $bill)->with('status', 'Measurement sheet saved successfully.');
    }

    public function print(Bill $bill): View
    {
        $this->authorize('printMeasurement', $bill);

        $bill->load(['measurementItems.lines', 'department']);

        return view('bills.measurement-print', ['bill' => $bill]);
    }

    public function pdf(Bill $bill): Response
    {
        $this->authorize('printMeasurement', $bill);

        $bill->load(['measurementItems.lines', 'department']);

        return Pdf::loadView('bills.measurement-pdf', ['bill' => $bill])
            ->download("measurement-sheet-bill-{$bill->invoice_no}.pdf");
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function groupsForForm(Bill $bill): array
    {
        $groups = $bill->measurementItems->map(fn ($item) => [
            'total' => $item->total !== null ? (float) $item->total : '',
            'total_text' => $item->total_text ?? '',
            'total_unit' => $item->total_unit ?? '',
            'lines' => $item->lines->map(fn ($line) => [
                'service_no' => $line->service_no ?? '',
                'description' => $line->description ?? '',
                'no' => $line->no ?? '',
                'length' => $line->length ?? '',
                'breath' => $line->breath ?? '',
                'unit' => $line->unit ?? '',
                'quantity' => $line->quantity ?? '',
            ])->all(),
        ])->all();

        return $groups !== [] ? $groups : [[
            'total' => '', 'total_text' => '', 'total_unit' => '',
            'lines' => [['service_no' => '', 'description' => '', 'no' => '', 'length' => '', 'breath' => '', 'unit' => '', 'quantity' => '']],
        ]];
    }
}
