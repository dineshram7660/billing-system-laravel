<?php

namespace App\Http\Controllers;

use App\Models\Bill;
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
 *
 * Not ported: the "Copy Measurement" convenience that appends another
 * estimate's measurement sheet onto this bill's — a nice-to-have layered
 * on top of the core editor, not the editor itself.
 */
class MeasurementBillController extends Controller
{
    public function edit(Bill $bill): View
    {
        Gate::authorize('edit-measurement');

        $bill->load('measurementItems.lines');

        return view('bills.measurement', ['bill' => $bill, 'groups' => $this->groupsForForm($bill)]);
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
