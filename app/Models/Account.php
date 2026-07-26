<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['account_name', 'username', 'password', 'status', 'employee', 'par_day', 'designation_id'])]
#[Hidden(['password'])]
class Account extends Model
{
    protected $table = 'account';

    public $timestamps = false;

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(AccountDetail::class, 'account_id');
    }
}
