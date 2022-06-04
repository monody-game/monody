<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Redis;

class GameExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        return (bool) Redis::exists("game:$value");
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Game with given id does not exists.';
    }
}
