<?php

namespace App\Rules;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Weekday implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Carbon::parse($value)->dayOfWeek > CarbonInterface::FRIDAY) {
            $fail('The :attribute cannot be on the weekend.');
        }
    }
}
