<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmployeePolicy extends LegacyModulePolicy
{
    protected string $module = 'Employee';

    /**
     * Legacy splits list access ("Employee") from viewing a single
     * employee's Credit/Debit ledger ("View Employee") — same pattern
     * as AccountPolicy::view().
     */
    public function view(User $user, Model $model): bool
    {
        return $user->hasLegacyPermission('View Employee');
    }
}
