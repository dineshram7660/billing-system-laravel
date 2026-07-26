<?php

namespace App\Http\Controllers;

use App\Http\Requests\DesignationRequest;
use App\Models\Designation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DesignationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Designation::class);

        $designations = Designation::orderBy('designation_name')->paginate(20);

        return view('designations.index', compact('designations'));
    }

    public function create(): View
    {
        $this->authorize('create', Designation::class);

        return view('designations.form', ['designation' => new Designation]);
    }

    public function store(DesignationRequest $request): RedirectResponse
    {
        $this->authorize('create', Designation::class);

        Designation::create($request->validated());

        return redirect()->route('designations.index')->with('status', 'Designation added successfully.');
    }

    public function edit(Designation $designation): View
    {
        $this->authorize('update', $designation);

        return view('designations.form', compact('designation'));
    }

    public function update(DesignationRequest $request, Designation $designation): RedirectResponse
    {
        $this->authorize('update', $designation);

        $designation->update($request->validated());

        return redirect()->route('designations.index')->with('status', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation): RedirectResponse
    {
        $this->authorize('delete', $designation);

        $designation->delete();

        return redirect()->route('designations.index')->with('status', 'Designation deleted successfully.');
    }
}
