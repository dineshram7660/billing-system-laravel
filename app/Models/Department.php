<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['department_name'])]
class Department extends Model
{
    protected $table = 'department';

    public $timestamps = false;

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'd_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'd_id');
    }
}
