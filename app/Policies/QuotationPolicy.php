<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy extends LegacyModulePolicy
{
    protected string $module = 'Quotation';

    public function print(User $user, Quotation $quotation): bool
    {
        return $user->hasLegacyPermission('Print Quotation');
    }
}
