<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy extends LegacyModulePolicy
{
    protected string $module = 'Bill';

    public function print(User $user, Bill $bill): bool
    {
        return $user->hasLegacyPermission('Print Bill');
    }
}
