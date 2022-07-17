<?php

namespace App\Rules;

use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class PlayerNotAliveRule implements Rule, DataAwareRule
{
    use MemberHelperTrait;
    use RegisterHelperTrait;

    /**
     * @var string[]
     */
    private array $data;

    public function passes($attribute, $value): bool
    {
        $gameId = \array_key_exists('gameId', $this->data) ? $this->data['gameId'] : $this->getCurrentUserGameActivity($value);

        return !$this->alive($value, $gameId ?: '');
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
