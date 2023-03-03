<?php

namespace App\Rules;

use App\Traits\InteractsWithRedis;
use Illuminate\Contracts\Validation\Rule;

readonly class GameExistsRule implements Rule
{
    use InteractsWithRedis;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        return $this->redis()->exists("game:$value");
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Game with given id does not exists.';
    }
}
