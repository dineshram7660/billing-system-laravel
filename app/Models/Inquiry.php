<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['fname', 'lname', 'email', 'subject', 'message', 'date'])]
class Inquiry extends Model
{
    // Table name keeps the legacy spelling ("inquery") on purpose — the
    // class is named correctly so nothing else has to carry the typo.
    protected $table = 'inquery';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
