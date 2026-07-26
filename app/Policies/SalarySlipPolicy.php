<?php

namespace App\Policies;

use App\Models\SalarySlip;
use App\Models\User;

class SalarySlipPolicy extends LegacyModulePolicy
{
    protected string $module = 'Salary Slip';

    public function print(User $user, SalarySlip $salarySlip): bool
    {
        return $user->hasLegacyPermission('Print Salary Slip');
    }
}
