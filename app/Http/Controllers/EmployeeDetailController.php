<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeDetailRequest;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use Illuminate\Http\RedirectResponse;

class EmployeeDetailController extends Controller
{
    public function store(EmployeeDetailRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('create', EmployeeDetail::class);

        $employee->details()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('status', 'Transaction added successfully.');
    }

    public function destroy(Employee $employee, EmployeeDetail $detail): RedirectResponse
    {
        $this->authorize('delete', $detail);

        $detail->delete();

        return redirect()->route('employees.show', $employee)->with('status', 'Transaction deleted successfully.');
    }
}
