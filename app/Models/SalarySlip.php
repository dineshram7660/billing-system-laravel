<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id', 'day_work', 'par_day', 'over_time', 'pf_amount', 'extra_pay',
    'salary_slip_date', 'salary_slip_month', 'salary_slip_year',
    'advance_payment', 'advance_payment_earnings', 'total_advance_payment',
    'professional_tax',
])]
class SalarySlip extends Model
{
    protected $table = 'salary_slip';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['salary_slip_date' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
