<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'par_day_amount', 'per_day_extra', 'date'])]
class SalaryDetail extends Model
{
    protected $table = 'salary_details';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'par_day_amount' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
