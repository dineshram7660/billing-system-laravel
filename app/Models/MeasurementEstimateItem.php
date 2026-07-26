<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['estimate_id', 'total', 'total_text', 'total_unit', 'sort_order'])]
class MeasurementEstimateItem extends Model
{
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(MeasurementEstimateItemLine::class)->orderBy('sort_order');
    }
}
