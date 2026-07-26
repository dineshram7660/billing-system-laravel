<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::with('designation')->orderBy('employee_name')->paginate(20);

        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('employees.form', [
            'employee' => new Employee,
            'designations' => Designation::orderBy('designation_name')->get(),
        ]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        Employee::create($request->validated());

        return redirect()->route('employees.index')->with('status', 'Employee added successfully.');
    }

    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);

        $details = $employee->details()->orderByDesc('date')->orderByDesc('id')->paginate(20);

        $debit = (float) $employee->details()->where('type', 'Debit')->sum('amount');
        $credit = (float) $employee->details()->where('type', 'Credit')->sum('amount');

        return view('employees.show', [
            'employee' => $employee,
            'details' => $details,
            'balance' => $debit - $credit,
        ]);
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        return view('employees.form', [
            'employee' => $employee,
            'designations' => Designation::orderBy('designation_name')->get(),
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $employee->update($request->validated());

        return redirect()->route('employees.index')->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return redirect()->route('employees.index')->with('status', 'Employee deleted successfully.');
    }
}
