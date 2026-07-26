<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Maps onto the legacy `admin` table rather than a fresh `users` table, so
 * existing admin accounts and their permissions keep working unchanged.
 *
 * `type` mirrors the legacy app: 0 = full admin, 1 = sub admin.
 * `access` is a comma-separated list of permission names, same as the
 * legacy `sub_admin_access()` check — see App\Models\Concerns or the
 * Gate definitions in AppServiceProvider.
 */
#[Fillable(['first_name', 'last_name', 'email', 'password', 'type', 'access'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'admin';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Breeze's default Blade views (nav dropdown, profile page) expect a
     * single `name` attribute; the legacy table splits it in two.
     */
    protected function name(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function isFullAdmin(): bool
    {
        return (int) $this->type === 0;
    }

    /**
     * @return array<int, string>
     */
    public function permissions(): array
    {
        if ($this->isFullAdmin()) {
            return ['*'];
        }

        return array_filter(array_map('trim', explode(',', (string) $this->access)));
    }

    /**
     * Mirrors the legacy `sub_admin_access($permission, $admin_data)` check.
     * Kept separate from the standard can()/Gate flow, which Phase 2's
     * per-model Policies will use instead.
     */
    public function hasLegacyPermission(string $permission): bool
    {
        if ($this->isFullAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissions(), true);
    }
}
