<?php

namespace App\Policies;

use App\Models\AccountDetail;
use App\Models\User;

/**
 * Credit/Debit ledger entries for an Account — see
 * add_edit_c_d_account.php/view_account.php. Bespoke like
 * SalaryDetailPolicy: the legacy permission names ("Credit Debit
 * Account", "Delete Account Tranjection") don't fit the $module + four-
 * ability LegacyModulePolicy pattern.
 */
class AccountDetailPolicy
{
    public function create(User $user): bool
    {
        return $user->hasLegacyPermission('Credit Debit Account');
    }

    public function update(User $user, AccountDetail $accountDetail): bool
    {
        return $user->hasLegacyPermission('Credit Debit Account');
    }

    public function delete(User $user, AccountDetail $accountDetail): bool
    {
        return $user->hasLegacyPermission('Delete Account Tranjection');
    }
}
