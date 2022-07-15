<?php

namespace App\Rules;

use App\Traits\MemberHelperTrait;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class PlayerAliveRule implements Rule, DataAwareRule
{
    use MemberHelperTrait;

    /**
     * @var string[]
     */
    private array $data;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value     User id
     */
    public function passes($attribute, $value): bool
    {
        $gameId = 'gameId' === $attribute ? $value : $this->data['gameId'];

        return $this->alive($value, $gameId);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Player is not alive.';
    }

    /**
     * @param string[] $data
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
