<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillRequest;
use App\Models\Bill;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Bill::class);

        $year = (int) (request('year') ?? $this->currentFiscalYearStart());

        $bills = Bill::with('department')
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('sir_name', 'like', "%{$search}%")
                        ->orWhere('ref_no', 'like', "%{$search}%");
                });
            })
            ->when(request('from_date') && request('to_date'), function ($query) {
                $query->whereBetween('bill_date', [request('from_date'), request('to_date')]);
            }, function ($query) use ($year) {
                // Default: India's April–March fiscal year, matching legacy bill.php.
                $query->whereBetween('bill_date', ["{$year}-04-01", ($year + 1).'-03-31']);
            })
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('bills.index', [
            'bills' => $bills,
            'year' => $year,
            'years' => range(2017, (int) now()->format('Y') + 1),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Bill::class);

        $bill = new Bill([
            'invoice_no' => (int) Bill::max('invoice_no') + 1,
            'gst_bill' => 1,
            'bill_date' => now()->toDateString(),
        ]);

        return view('bills.form', [
            'bill' => $bill,
            'departments' => Department::orderBy('department_name')->get(),
            'items' => [],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function itemsForForm(Bill $bill): array
    {
        return $bill->items()->get()->map(fn ($item) => [
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

    public function store(BillRequest $request): RedirectResponse
    {
        $this->authorize('create', Bill::class);

        $items = $this->normalizeItems($request->input('items'));

        $bill = DB::transaction(function () use ($request, $items) {
            $bill = Bill::create([
                ...$request->safe()->except('items'),
                'total' => collect($items)->sum('total'),
            ]);

            $this->syncItems($bill, $items);

            return $bill;
        });

        return redirect()->route('bills.index')->with('status', "Bill #{$bill->invoice_no} saved successfully.");
    }

    public function edit(Bill $bill): View
    {
        $this->authorize('update', $bill);

        return view('bills.form', [
            'bill' => $bill,
            'departments' => Department::orderBy('department_name')->get(),
            'items' => $this->itemsForForm($bill),
        ]);
    }

    public function update(BillRequest $request, Bill $bill): RedirectResponse
    {
        $this->authorize('update', $bill);

        $items = $this->normalizeItems($request->input('items'));

        DB::transaction(function () use ($request, $bill, $items) {
            $bill->update([
                ...$request->safe()->except('items'),
                'total' => collect($items)->sum('total'),
            ]);

            $this->syncItems($bill, $items);
        });

        return redirect()->route('bills.index')->with('status', "Bill #{$bill->invoice_no} updated successfully.");
    }

    public function destroy(Bill $bill): RedirectResponse
    {
        $this->authorize('delete', $bill);

        $bill->delete();

        return redirect()->route('bills.index')->with('status', 'Bill deleted successfully.');
    }

    public function print(Bill $bill): View
    {
        $this->authorize('print', $bill);

        $bill->load(['items', 'department']);

        $cgst = $bill->gst_bill ? round((float) $bill->total * config('company.cgst_rate') / 100) : 0;
        $sgst = $bill->gst_bill ? round((float) $bill->total * config('company.sgst_rate') / 100) : 0;
        $grandTotal = round((float) $bill->total + $cgst + $sgst);

        return view('bills.print', [
            'bill' => $bill,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'grandTotal' => $grandTotal,
        ]);
    }

    /**
     * Recomputes each line's total as price × qty server-side — the
     * legacy form trusted whatever the client posted for both the line
     * total and the bill subtotal (see the migration research notes);
     * this closes that gap rather than porting it.
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
    private function syncItems(Bill $bill, array $items): void
    {
        $bill->items()->delete();

        foreach (array_values($items) as $sortOrder => $item) {
            $bill->items()->create([
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

    private function currentFiscalYearStart(): int
    {
        $now = now();

        return (int) ($now->month >= 4 ? $now->year : $now->year - 1);
    }
}
