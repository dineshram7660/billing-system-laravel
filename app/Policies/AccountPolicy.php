<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AccountPolicy extends LegacyModulePolicy
{
    protected string $module = 'Account';

    /**
     * Legacy splits list access ("Account") from viewing a single
     * account's ledger ("View Account") — LegacyModulePolicy's default
     * view() just delegates to viewAny(), which would conflate the two.
     */
    public function view(User $user, Model $model): bool
    {
        return $user->hasLegacyPermission('View Account');
    }
}
