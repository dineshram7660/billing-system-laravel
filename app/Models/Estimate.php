<?php

namespace App\Models;

use App\Casts\LegacyDate;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `product` is still the legacy [#]/[@]-delimited text blob at this stage
 * of the migration — see Bill for the same note and the roadmap's Phase 4.
 */
#[Fillable(['subject', 'ast_desc', 'product', 'total', 'bill_date', 'address'])]
class Estimate extends Model
{
    protected $table = 'estimate';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'bill_date' => LegacyDate::class,
            'total' => 'decimal:2',
        ];
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(MeasurementEstimate::class, 'e_id');
    }

    public function billEstimates(): HasMany
    {
        return $this->hasMany(BillEstimate::class, 'e_id');
    }

    public function emailSends(): HasMany
    {
        return $this->hasMany(EmailSend::class, 'all_id')->where('type', 'Estimate');
    }
}
