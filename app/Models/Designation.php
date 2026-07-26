<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['designation_name'])]
class Designation extends Model
{
    protected $table = 'designation';

    public $timestamps = false;

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'designation_id');
    }
}
