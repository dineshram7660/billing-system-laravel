<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Department::class);

        $departments = Department::orderBy('department_name')->paginate(20);

        return view('departments.index', compact('departments'));
    }

    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('departments.form', ['department' => new Department]);
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        Department::create($request->validated());

        return redirect()->route('departments.index')->with('status', 'Department added successfully.');
    }

    public function edit(Department $department): View
    {
        $this->authorize('update', $department);

        return view('departments.form', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $department->update($request->validated());

        return redirect()->route('departments.index')->with('status', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return redirect()->route('departments.index')->with('status', 'Department deleted successfully.');
    }
}
