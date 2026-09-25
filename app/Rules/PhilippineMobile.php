<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A Philippine mobile number: 10 digits starting with 9 once the +63 / 0 prefix
 * and any spaces or dashes are taken off. "0917 123 4567", "+63 917-123-4567"
 * and "9171234567" all pass; an eleventh digit or a landline does not.
 */
class PhilippineMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^9\d{9}$/', User::normalizePhone((string) $value))) {
            $fail('Enter a Philippine mobile number: 10 digits after +63, starting with 9 (e.g. 912 345 6789).');
        }
    }
}
