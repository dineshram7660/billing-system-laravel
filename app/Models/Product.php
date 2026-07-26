<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_name', 'price', 'service_no', 'hsn_code', 'per_unit'])]
class Product extends Model
{
    protected $table = 'product';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }
}
