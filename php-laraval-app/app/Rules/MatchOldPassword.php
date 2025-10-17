<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class MatchOldPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::where('email', request()->input('current_email'))->first();
        if (Hash::check($value, $user->password)) {
            $fail('must match current password')->translate();
        }
    }
}
