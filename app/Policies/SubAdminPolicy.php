<?php

namespace App\Policies;

use App\Models\User;

/**
 * Doesn't extend LegacyModulePolicy: this module's legacy permission names
 * put "Sub Admin" first ("Sub Admin Edit", "Sub Admin Delete") rather than
 * following the "Edit {Module}" / "Delete {Module}" pattern every other
 * module uses. See sub_access table.
 */
class SubAdminPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasLegacyPermission('Sub Admin');
    }

    public function view(User $user, User $subAdmin): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasLegacyPermission('Add New Sub Admin');
    }

    public function update(User $user, User $subAdmin): bool
    {
        return $user->hasLegacyPermission('Sub Admin Edit');
    }

    public function delete(User $user, User $subAdmin): bool
    {
        return $user->hasLegacyPermission('Sub Admin Delete') && $user->isNot($subAdmin);
    }
}
