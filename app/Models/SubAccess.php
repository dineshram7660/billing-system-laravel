<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Defines the permission names an admin's `access` column (a comma
 * separated list, see User::permissions()) can reference — the legacy
 * app's version of a permissions table.
 */
#[Fillable(['name', 'class', 'extra', 'add_row'])]
class SubAccess extends Model
{
    protected $table = 'sub_access';

    public $timestamps = false;
}
