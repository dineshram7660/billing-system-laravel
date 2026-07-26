<?php

namespace App\Policies;

use App\Models\EmployeeDetail;
use App\Models\User;

/**
 * Credit/Debit ledger entries for an Employee — see
 * add_edit_c_d_employee.php/view_employee.php. Bespoke like
 * AccountDetailPolicy/SalaryDetailPolicy: the legacy permission names
 * ("Credit Debit Employee", "Delete Employee Tranjection") don't fit the
 * $module + four-ability LegacyModulePolicy pattern.
 */
class EmployeeDetailPolicy
{
    public function create(User $user): bool
    {
        return $user->hasLegacyPermission('Credit Debit Employee');
    }

    public function update(User $user, EmployeeDetail $employeeDetail): bool
    {
        return $user->hasLegacyPermission('Credit Debit Employee');
    }

    public function delete(User $user, EmployeeDetail $employeeDetail): bool
    {
        return $user->hasLegacyPermission('Delete Employee Tranjection');
    }
}
