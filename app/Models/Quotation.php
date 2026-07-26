<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['quotation_to', 'subject', 'particulars', 'unit', 'total', 'bill_date'])]
class Quotation extends Model
{
    protected $table = 'quotation';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'bill_date' => 'date',
            'total' => 'decimal:2',
        ];
    }
}
