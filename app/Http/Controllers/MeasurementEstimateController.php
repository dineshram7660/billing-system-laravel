<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Rebuilds add_edit_estimate_measurement.php/estimate_measurement_print.php
 * — structurally identical to MeasurementBillController (confirmed by
 * diffing the two legacy files), operating on the already-normalized
 * measurement_estimate_items/measurement_estimate_item_lines tables.
 */
class MeasurementEstimateController extends Controller
{
    public function edit(Estimate $estimate): View
    {
        Gate::authorize('edit-measurement');

        $estimate->load('measurementItems.lines');

        return view('estimates.measurement', ['estimate' => $estimate, 'groups' => $this->groupsForForm($estimate)]);
    }

    public function update(Request $request, Estimate $estimate): RedirectResponse
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

        DB::transaction(function () use ($estimate, $validated) {
            $existingItemIds = $estimate->measurementItems()->pluck('id');
            DB::table('measurement_estimate_item_lines')->whereIn('measurement_estimate_item_id', $existingItemIds)->delete();
            $estimate->measurementItems()->delete();

            foreach (array_values($validated['groups']) as $groupOrder => $group) {
                $item = $estimate->measurementItems()->create([
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

        return redirect()->route('estimates.measurement.edit', $estimate)->with('status', 'Measurement sheet saved successfully.');
    }

    public function print(Estimate $estimate): View
    {
        $this->authorize('printMeasurement', $estimate);

        $estimate->load('measurementItems.lines');

        return view('estimates.measurement-print', ['estimate' => $estimate]);
    }

    public function pdf(Estimate $estimate): Response
    {
        $this->authorize('printMeasurement', $estimate);

        $estimate->load('measurementItems.lines');

        return Pdf::loadView('estimates.measurement-pdf', ['estimate' => $estimate])
            ->download("measurement-sheet-estimate-{$estimate->id}.pdf");
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function groupsForForm(Estimate $estimate): array
    {
        $groups = $estimate->measurementItems->map(fn ($item) => [
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
