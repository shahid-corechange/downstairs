<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserUniqueCellphone implements ValidationRule
{
    public function __construct(
        private ?User $user = null,
    ) {
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phones = explode(' ', $value);
        $dialCode = str_replace('+', '', $phones[0]);
        $cellphone = $dialCode.$phones[1];

        if ($this->user) {
            $exists = User::whereNot('id', $this->user->id)
                ->where('cellphone', $cellphone)->exists();

            if ($exists) {
                $fail('already exists')->translate();
            }
        } else {
            $exists = User::where('cellphone', $cellphone)->exists();

            if ($exists) {
                $fail('already exists')->translate();
            }
        }
    }
}
