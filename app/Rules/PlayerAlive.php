<?php

namespace App\Rules;

use App\Traits\MemberHelperTrait;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class PlayerAlive implements Rule, DataAwareRule
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

        $member = $this->getMember($value, $gameId);

        if (!$member) {
            return false;
        }

        if (
            \array_key_exists('is_dead', $member['user_info']) &&
            true === $member['user_info']['is_dead']
        ) {
            return false;
        }

        return true;
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
