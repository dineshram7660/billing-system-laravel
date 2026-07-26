<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['filename', 'date', 'type'])]
class Report extends Model
{
    protected $table = 'report';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
