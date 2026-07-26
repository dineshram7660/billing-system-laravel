<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['d_id', 'amount', 'expenses_date', 'description'])]
class Expense extends Model
{
    protected $table = 'expenses';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'expenses_date' => 'date',
            'amount' => 'float',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'd_id');
    }
}
