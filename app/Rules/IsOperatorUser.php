<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsOperatorUser implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         $user = User::with('role')->find($value);
         if (
             ! $user ||
             ! $user->role ||
             strtolower($user->role->name) !== 'operator'
         ) {
             $fail('User yang dipilih bukan operator.');
         }
    }
}
