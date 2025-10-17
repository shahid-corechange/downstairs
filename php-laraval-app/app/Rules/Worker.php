<?php

namespace App\Rules;

use App\Enums\PermissionsEnum;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Worker implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);

        if (! $user->can(PermissionsEnum::AccessEmployeeApp())) {
            $fail("not has role 'worker'")->translate();
        }
    }
}
