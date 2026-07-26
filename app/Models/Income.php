<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['d_id', 'amount', 'income_date'])]
class Income extends Model
{
    protected $table = 'income';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'income_date' => 'date',
            'amount' => 'float',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'd_id');
    }
}
