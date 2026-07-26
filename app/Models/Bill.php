<?php

namespace App\Models;

use App\Casts\LegacyDate;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `product` is the legacy [#]/[@]-delimited text blob — kept as a
 * read-only fallback/audit trail, but don't build new features against it.
 * Use items() instead, backfilled from this column by
 * App\Console\Commands\ImportLegacyLineItems. A handful of bills (~4%)
 * have malformed product data that couldn't be backfilled automatically —
 * see the legacy_import_issues table — so items() can legitimately be
 * empty for a bill that has a non-trivial `product` value; check there
 * before assuming a bug.
 */
#[Fillable([
    'invoice_no', 'subject', 'gst_no', 'ref_no', 'ref_date', 'product',
    'total', 'bill_date', 'paid', 'paid_amount', 'paid_date', 'd_id',
    'sir_name', 'remark', 'photo', 'address', 'bill_state', 'gst_bill',
])]
class Bill extends Model
{
    protected $table = 'bill';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'ref_date' => LegacyDate::class,
            'bill_date' => LegacyDate::class,
            'paid_date' => LegacyDate::class,
            'total' => 'decimal:2',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'd_id');
    }

    /**
     * @deprecated Raw legacy row (still the delimited "product" string).
     *  Use measurementItems() instead.
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(MeasurementBill::class, 'b_id');
    }

    public function measurementItems(): HasMany
    {
        return $this->hasMany(MeasurementBillItem::class, 'bill_id')->orderBy('sort_order');
    }

    public function employeeDetails(): HasMany
    {
        return $this->hasMany(EmployeeDetail::class, 'bill_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class, 'bill_id')->orderBy('sort_order');
    }
}
