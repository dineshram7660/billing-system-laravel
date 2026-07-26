<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstimateRequest;
use App\Models\Estimate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EstimateController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Estimate::class);

        $estimates = Estimate::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('subject', 'like', "%{$search}%");
            })
            ->when(request('from_date') && request('to_date'), function ($query) {
                $query->whereBetween('bill_date', [request('from_date'), request('to_date')]);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('estimates.index', ['estimates' => $estimates]);
    }

    public function create(): View
    {
        $this->authorize('create', Estimate::class);

        return view('estimates.form', [
            'estimate' => new Estimate(['bill_date' => now()->toDateString()]),
            'items' => [],
        ]);
    }

    public function store(EstimateRequest $request): RedirectResponse
    {
        $this->authorize('create', Estimate::class);

        $items = $this->normalizeItems($request->input('items'));

        $estimate = DB::transaction(function () use ($request, $items) {
            $estimate = Estimate::create([
                ...$request->safe()->except('items'),
                'total' => collect($items)->sum('total'),
            ]);

            $this->syncItems($estimate, $items);

            return $estimate;
        });

        return redirect()->route('estimates.index')->with('status', "Estimate \"{$estimate->subject}\" saved successfully.");
    }

    public function edit(Estimate $estimate): View
    {
        $this->authorize('update', $estimate);

        return view('estimates.form', [
            'estimate' => $estimate,
            'items' => $this->itemsForForm($estimate),
        ]);
    }

    public function update(EstimateRequest $request, Estimate $estimate): RedirectResponse
    {
        $this->authorize('update', $estimate);

        $items = $this->normalizeItems($request->input('items'));

        DB::transaction(function () use ($request, $estimate, $items) {
            $estimate->update([
                ...$request->safe()->except('items'),
                'total' => collect($items)->sum('total'),
            ]);

            $this->syncItems($estimate, $items);
        });

        return redirect()->route('estimates.index')->with('status', "Estimate \"{$estimate->subject}\" updated successfully.");
    }

    public function destroy(Estimate $estimate): RedirectResponse
    {
        $this->authorize('delete', $estimate);

        $estimate->delete();

        return redirect()->route('estimates.index')->with('status', 'Estimate deleted successfully.');
    }

    public function print(Estimate $estimate): View
    {
        $this->authorize('print', $estimate);

        return view('estimates.print', $this->printData($estimate));
    }

    public function pdf(Estimate $estimate): Response
    {
        $this->authorize('print', $estimate);

        return Pdf::loadView('estimates.pdf', $this->printData($estimate))
            ->download("estimate-{$estimate->id}.pdf");
    }

    /**
     * @return array{estimate: Estimate, cgst: float, sgst: float, grandTotal: float}
     */
    private function printData(Estimate $estimate): array
    {
        $estimate->load('items');

        // Unlike Bill, the legacy estimate_print.php has no GST toggle —
        // CGST/SGST are always applied at 9%+9%, see the migration
        // research notes.
        $cgst = round((float) $estimate->total * config('company.cgst_rate') / 100);
        $sgst = round((float) $estimate->total * config('company.sgst_rate') / 100);
        $grandTotal = round((float) $estimate->total + $cgst + $sgst);

        return [
            'estimate' => $estimate,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'grandTotal' => $grandTotal,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function itemsForForm(Estimate $estimate): array
    {
        return $estimate->items()->get()->map(fn ($item) => [
            'product_id' => $item->product_id,
            'service_no' => $item->service_no ?? '',
            'product_name' => $item->product_name,
            'hsn_code' => $item->hsn_code ?? '',
            'per_unit' => $item->per_unit ?? '',
            'price' => (float) $item->price,
            'qty' => (float) $item->qty,
            'total' => (float) $item->total,
        ])->all();
    }

    /**
     * Recomputes each line's total as price × qty server-side — see the
     * identical note on BillController::normalizeItems().
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        return array_map(function (array $item) {
            $qty = (float) $item['qty'];
            $price = (float) $item['price'];

            return [
                ...$item,
                'total' => round($price * $qty, 2),
            ];
        }, $items);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Estimate $estimate, array $items): void
    {
        $estimate->items()->delete();

        foreach (array_values($items) as $sortOrder => $item) {
            $estimate->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'service_no' => $item['service_no'] ?? null,
                'product_name' => $item['product_name'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'per_unit' => $item['per_unit'] ?? null,
                'price' => $item['price'],
                'qty' => $item['qty'],
                'total' => $item['total'],
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
