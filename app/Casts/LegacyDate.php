<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * The legacy app stored "no date yet" as the literal string '0000-00-00'
 * rather than NULL (see admin/add_edit_bill.php's validateDate() fallback
 * in the old codebase). Carbon doesn't reject that string — it silently
 * parses it into a nonsense date instead — so treat it as null here rather
 * than reaching for the built-in 'date' cast.
 */
class LegacyDate implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if (empty($value) || str_starts_with((string) $value, '0000-00-00')) {
            return null;
        }

        return Carbon::parse($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $value instanceof Carbon
            ? $value->toDateString()
            : (string) $value;
    }
}
