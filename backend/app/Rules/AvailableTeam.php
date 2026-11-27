<?php

namespace App\Rules;

use App\Models\Team;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The team exists, is active, and is not already dispatched to an open incident.
 */
class AvailableTeam implements ValidationRule
{
    public const MESSAGE = 'This team is not available. It is inactive or already dispatched to an open incident.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value) || ! Team::query()->availableForDispatch()->whereKey((int) $value)->exists()) {
            $fail(self::MESSAGE);
        }
    }
}
