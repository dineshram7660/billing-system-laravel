<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['e_id', 'product'])]
class BillEstimate extends Model
{
    protected $table = 'bill_estimate';

    public $timestamps = false;

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'e_id');
    }
}
