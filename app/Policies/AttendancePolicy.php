<?php

namespace App\Policies;

use App\Models\User;

/**
 * Doesn't extend LegacyModulePolicy: legacy's permission is "Add
 * Attendance", not "Add New Attendance" — the only module that drops
 * "New" from that convention.
 */
class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasLegacyPermission('Attendance');
    }

    public function create(User $user): bool
    {
        return $user->hasLegacyPermission('Add Attendance');
    }

    public function update(User $user): bool
    {
        return $user->hasLegacyPermission('Edit Attendance');
    }

    public function delete(User $user): bool
    {
        return $user->hasLegacyPermission('Delete Attendance');
    }
}
