<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Log of PDF/report emails sent to clients (estimate, bill, GST, etc.).
 * `all_id` points at the relevant Bill/Estimate id depending on `type`,
 * so it isn't a single consistent foreign key to model as a relation.
 */
#[Fillable(['client_name', 'email', 'file_name', 'date', 'measurement', 'all_id', 'type'])]
class EmailSend extends Model
{
    protected $table = 'email_send';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
