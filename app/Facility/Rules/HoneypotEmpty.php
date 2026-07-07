<?php

declare(strict_types=1);

namespace App\Facility\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class HoneypotEmpty implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! empty($value)) {
            $fail('Permintaan tidak valid.');
        }
    }
}
