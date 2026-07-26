<?php

namespace App\Policies;

use App\Models\Inquiry;
use App\Models\User;

/**
 * Inquiries come from a public contact form, not an admin form — legacy
 * has no add/edit page for this table, only a list + delete
 * (`inquery.php`). Permission names keep the legacy "Inquery" spelling.
 */
class InquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasLegacyPermission('Inquery');
    }

    public function view(User $user, Inquiry $inquiry): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Inquiry $inquiry): bool
    {
        return $user->hasLegacyPermission('Delete Inquery');
    }
}
