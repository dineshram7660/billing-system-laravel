<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Quotation::class);

        $quotations = Quotation::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('subject', 'like', "%{$search}%")
                        ->orWhere('quotation_to', 'like', "%{$search}%")
                        ->orWhere('particulars', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('quotations.index', compact('quotations'));
    }

    public function create(): View
    {
        $this->authorize('create', Quotation::class);

        return view('quotations.form', [
            'quotation' => new Quotation(['bill_date' => now()->toDateString()]),
        ]);
    }

    public function store(QuotationRequest $request): RedirectResponse
    {
        $this->authorize('create', Quotation::class);

        Quotation::create($request->validated());

        return redirect()->route('quotations.index')->with('status', 'Quotation added successfully.');
    }

    public function edit(Quotation $quotation): View
    {
        $this->authorize('update', $quotation);

        return view('quotations.form', compact('quotation'));
    }

    public function update(QuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        $quotation->update($request->validated());

        return redirect()->route('quotations.index')->with('status', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorize('delete', $quotation);

        $quotation->delete();

        return redirect()->route('quotations.index')->with('status', 'Quotation deleted successfully.');
    }

    public function print(Quotation $quotation): View
    {
        $this->authorize('print', $quotation);

        return view('quotations.print', compact('quotation'));
    }
}
