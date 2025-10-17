<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class EmployeeName implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = Validator::make([$attribute => $value], [$attribute => 'regex:/^[^!@#$%^&*()\-_\/:]+$/']);

        if (! $validator->passes()) {
            $fail('employee name field format is invalid')
                ->translate(['chars' => '! @ # $ % ^ & * ( ) - _ / :']);
        }
    }
}
