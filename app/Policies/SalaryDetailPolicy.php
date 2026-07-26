<?php

namespace App\Policies;

use App\Models\SalaryDetail;
use App\Models\User;

/**
 * Salary rate history (par_day_amount/per_day_extra over time), nested
 * under an employee — see view_salary.php/add_edit_salary.php in the
 * legacy app. Doesn't fit LegacyModulePolicy's "$module" + 4-ability
 * pattern: the legacy permission names are "View Salary", "Edit Salary",
 * and "Delete Salary Tranjection" (sic — not "Add New Salary Rate" etc.),
 * and there's no per-row "view"/"update" distinct from the list.
 */
class SalaryDetailPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasLegacyPermission('View Salary');
    }

    public function create(User $user): bool
    {
        return $user->hasLegacyPermission('Edit Salary');
    }

    public function delete(User $user, SalaryDetail $salaryDetail): bool
    {
        return $user->hasLegacyPermission('Delete Salary Tranjection');
    }
}
