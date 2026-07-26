<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeRequest;
use App\Models\Department;
use App\Models\Income;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Income::class);

        $incomes = Income::with('department')
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('incomes.index', compact('incomes'));
    }

    public function create(): View
    {
        $this->authorize('create', Income::class);

        return view('incomes.form', [
            'income' => new Income(['income_date' => now()->toDateString()]),
            'departments' => Department::orderBy('department_name')->get(),
        ]);
    }

    public function store(IncomeRequest $request): RedirectResponse
    {
        $this->authorize('create', Income::class);

        Income::create($request->validated());

        return redirect()->route('incomes.index')->with('status', 'Income added successfully.');
    }

    public function edit(Income $income): View
    {
        $this->authorize('update', $income);

        return view('incomes.form', [
            'income' => $income,
            'departments' => Department::orderBy('department_name')->get(),
        ]);
    }

    public function update(IncomeRequest $request, Income $income): RedirectResponse
    {
        $this->authorize('update', $income);

        $income->update($request->validated());

        return redirect()->route('incomes.index')->with('status', 'Income updated successfully.');
    }

    public function destroy(Income $income): RedirectResponse
    {
        $this->authorize('delete', $income);

        $income->delete();

        return redirect()->route('incomes.index')->with('status', 'Income deleted successfully.');
    }
}
