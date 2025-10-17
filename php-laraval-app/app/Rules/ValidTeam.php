<?php

namespace App\Rules;

use App\Models\Team;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTeam implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $team = Team::find($value);

        if (! $team) {
            $fail('team does not exists')->translate();
        } elseif (! $team->users()->exists()) {
            $fail('unable to select team without workers')->translate();
        }
    }
}
