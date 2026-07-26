<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalaryDetailRequest;
use App\Models\Employee;
use App\Models\SalaryDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Pay-rate history for one employee (par_day_amount/per_day_extra, one
 * row per effective date — SalarySlipController::latestRate() picks the
 * most recent row on/before a given date). Rebuilds the essential subset
 * of view_salary.php/add_edit_salary.php: list, add, delete. Editing an
 * existing rate isn't ported — legacy's edit is rarely used differently
 * from adding a new dated row, so that's the only supported correction
 * path here; see the README.
 */
class SalaryDetailController extends Controller
{
    public function index(Employee $employee): View
    {
        $this->authorize('viewAny', SalaryDetail::class);

        $salaryDetails = $employee->salaryDetails()->orderByDesc('date')->orderByDesc('id')->get();

        return view('salary-details.index', compact('employee', 'salaryDetails'));
    }

    public function store(SalaryDetailRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('create', SalaryDetail::class);

        $employee->salaryDetails()->create($request->validated());

        return redirect()->route('employees.salary-details.index', $employee)->with('status', 'Pay rate added successfully.');
    }

    public function destroy(Employee $employee, SalaryDetail $salaryDetail): RedirectResponse
    {
        $this->authorize('delete', $salaryDetail);

        $salaryDetail->delete();

        return redirect()->route('employees.salary-details.index', $employee)->with('status', 'Pay rate deleted successfully.');
    }
}
