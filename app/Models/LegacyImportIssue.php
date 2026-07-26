<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source_table', 'source_id', 'reason', 'raw_value'])]
class LegacyImportIssue extends Model
{
    const UPDATED_AT = null;
}
