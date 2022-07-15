<?php

namespace App\Rules;

use App\Traits\MemberHelperTrait;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class PlayerNotAliveRule implements Rule, DataAwareRule
{
    use MemberHelperTrait;

    /**
     * @var string[]
     */
    private array $data;

    public function passes($attribute, $value): bool
    {
        $gameId = 'gameId' === $attribute ? $value : $this->data['gameId'];

        return !$this->alive($value, $gameId);
    }

    public function message(): string
    {
        return 'Player is alive.';
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
