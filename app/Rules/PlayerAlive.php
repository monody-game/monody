<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Redis;

class PlayerAlive implements Rule, DataAwareRule
{
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
        $key = "game:$gameId:members";

        if (!Redis::exists($key)) {
            return false;
        }

        $members = json_decode(Redis::get($key), true);
        $member = array_filter($members, fn ($member) => $member['user_id'] === $value);

        if (1 === \count($member)) {
            $member = $member[array_key_first($member)];
        }

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
