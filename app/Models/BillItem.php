<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bill_id', 'product_id', 'service_no', 'product_name',
    'hsn_code', 'per_unit', 'price', 'qty', 'total', 'sort_order',
])]
class BillItem extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'qty' => 'decimal:3',
            'total' => 'decimal:2',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
