<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bill_id', 'total', 'total_text', 'total_unit', 'sort_order'])]
class MeasurementBillItem extends Model
{
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(MeasurementBillItemLine::class)->orderBy('sort_order');
    }
}
