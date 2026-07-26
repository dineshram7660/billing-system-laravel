<?php

namespace App\Models;

use App\Casts\LegacyDate;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `product` is still the legacy [#]/[@]-delimited text blob at this stage
 * of the migration (see the roadmap's Phase 4: normalize the data model).
 * Don't build new features against it directly — it gets replaced by a
 * real billItems() relation once that migration lands.
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

    public function measurements(): HasMany
    {
        return $this->hasMany(MeasurementBill::class, 'b_id');
    }

    public function employeeDetails(): HasMany
    {
        return $this->hasMany(EmployeeDetail::class, 'bill_id');
    }
}
