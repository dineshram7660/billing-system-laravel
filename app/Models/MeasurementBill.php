<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['b_id', 'product'])]
class MeasurementBill extends Model
{
    protected $table = 'measurement_bill';

    public $timestamps = false;

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'b_id');
    }
}
