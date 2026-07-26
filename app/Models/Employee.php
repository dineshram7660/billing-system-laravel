<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'employee_name', 'username', 'password', 'status', 'employee',
    'par_day', 'designation_id', 'card_number', 'mobile_number', 'pf_number',
])]
#[Hidden(['password'])]
class Employee extends Model
{
    protected $table = 'employee';

    public $timestamps = false;

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(EmployeeDetail::class, 'employee_id');
    }

    public function salaryDetails(): HasMany
    {
        return $this->hasMany(SalaryDetail::class, 'employee_id');
    }

    public function salarySlips(): HasMany
    {
        return $this->hasMany(SalarySlip::class, 'employee_id');
    }
}
