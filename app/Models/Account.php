<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A named ledger account (e.g. "Cash", "Bank", a vendor name) — despite
 * sharing several column names with `employee` (username, password,
 * status, par_day, designation_id), the legacy add/edit form
 * (add_edit_account.php) only ever reads/writes `account_name`; grepped
 * the whole legacy admin/ tree to confirm the rest are never touched
 * anywhere, so they're left off `Fillable` here.
 */
#[Fillable(['account_name'])]
class Account extends Model
{
    protected $table = 'account';

    public $timestamps = false;

    public function details(): HasMany
    {
        return $this->hasMany(AccountDetail::class, 'account_id');
    }
}
