<?php

namespace App\Policies;

use App\Models\Estimate;
use App\Models\User;

class EstimatePolicy extends LegacyModulePolicy
{
    protected string $module = 'Estimate';

    public function print(User $user, Estimate $estimate): bool
    {
        return $user->hasLegacyPermission('Print Estimate');
    }
}
