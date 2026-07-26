<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubAdminAccessRequest;
use App\Http\Requests\SubAdminPasswordRequest;
use App\Http\Requests\SubAdminRequest;
use App\Models\SubAccess;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubAdminController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $subAdmins = User::where('type', 1)->orderBy('first_name')->paginate(20);

        return view('sub-admins.index', ['subAdmins' => $subAdmins]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('sub-admins.create', ['user' => new User]);
    }

    public function store(SubAdminRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        // Matches the legacy app's default: a temporary password derived
        // from the first name, which the sub admin can change once they
        // can log in (see updatePassword()).
        User::create([
            ...$request->validated(),
            'password' => $request->validated('first_name'),
            'type' => 1,
            'access' => '',
        ]);

        return redirect()->route('sub-admins.index')->with('status', 'Sub admin added successfully.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('sub-admins.edit', [
            'user' => $user,
            'permissions' => SubAccess::orderBy('id')->get(),
            'grantedPermissions' => $user->permissions(),
        ]);
    }

    public function update(SubAdminRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return redirect()->route('sub-admins.edit', $user)->with('status', 'Sub admin updated successfully.');
    }

    public function updatePassword(SubAdminPasswordRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update(['password' => $request->validated('password')]);

        return redirect()->route('sub-admins.edit', $user)->with('status', 'Password updated successfully.');
    }

    public function updateAccess(SubAdminAccessRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update(['access' => implode(',', $request->validated('sub_access') ?? [])]);

        return redirect()->route('sub-admins.edit', $user)->with('status', 'Permissions updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('sub-admins.index')->with('status', 'Sub admin deleted successfully.');
    }
}
