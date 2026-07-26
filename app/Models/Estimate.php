<?php

namespace App\Models;

use App\Casts\LegacyDate;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `product` is the legacy [#]/[@]-delimited text blob — see Bill for the
 * same note on items() as the replacement and legacy_import_issues for
 * rows that couldn't be backfilled automatically.
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

    /**
     * @deprecated Raw legacy row (still the delimited "product" string).
     *  Use measurementItems() instead.
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(MeasurementEstimate::class, 'e_id');
    }

    public function measurementItems(): HasMany
    {
        return $this->hasMany(MeasurementEstimateItem::class, 'estimate_id')->orderBy('sort_order');
    }

    public function billEstimates(): HasMany
    {
        return $this->hasMany(BillEstimate::class, 'e_id');
    }

    public function emailSends(): HasMany
    {
        return $this->hasMany(EmailSend::class, 'all_id')->where('type', 'Estimate');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class, 'estimate_id')->orderBy('sort_order');
    }
}
