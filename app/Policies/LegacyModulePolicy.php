<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * The legacy app checks four permission names per module — e.g. for
 * Department: "Department" (list/view), "Add New Department", "Edit
 * Department", "Delete Department" (see sub_access table). Concrete
 * policies just declare $module and inherit the four abilities from here.
 */
abstract class LegacyModulePolicy
{
    protected string $module;

    public function viewAny(User $user): bool
    {
        return $user->hasLegacyPermission($this->module);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasLegacyPermission("Add New {$this->module}");
    }

    public function update(User $user, Model $model): bool
    {
        return $user->hasLegacyPermission("Edit {$this->module}");
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->hasLegacyPermission("Delete {$this->module}");
    }
}
