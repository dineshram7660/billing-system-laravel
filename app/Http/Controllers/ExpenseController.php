<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Department;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Expense::class);

        $expenses = Expense::with('department')
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('amount', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('expenses_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('expenses.index', compact('expenses'));
    }

    public function create(): View
    {
        $this->authorize('create', Expense::class);

        return view('expenses.form', [
            'expense' => new Expense(['expenses_date' => now()->toDateString()]),
            'departments' => Department::orderBy('department_name')->get(),
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        Expense::create($request->validated());

        return redirect()->route('expenses.index')->with('status', 'Expense added successfully.');
    }

    public function edit(Expense $expense): View
    {
        $this->authorize('update', $expense);

        return view('expenses.form', [
            'expense' => $expense,
            'departments' => Department::orderBy('department_name')->get(),
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $expense->update($request->validated());

        return redirect()->route('expenses.index')->with('status', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Expense deleted successfully.');
    }
}
