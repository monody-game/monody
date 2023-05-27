<?php

namespace App\Rules;

use App\Enums\Role;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RejectMultipleUniqueRoles implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($value as $role) {
            $limit = Role::from($role)->limit();
            if ($limit !== -1 && array_count_values($value)[$role] > $limit) {
                $fail('Unique roles can\'t be used twice.');
            }
        }
    }
}
