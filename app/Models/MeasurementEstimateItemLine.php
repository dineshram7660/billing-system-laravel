<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'measurement_estimate_item_id', 'service_no', 'description', 'no',
    'length', 'breath', 'unit', 'quantity', 'sort_order',
])]
class MeasurementEstimateItemLine extends Model
{
    public function item(): BelongsTo
    {
        return $this->belongsTo(MeasurementEstimateItem::class, 'measurement_estimate_item_id');
    }
}
