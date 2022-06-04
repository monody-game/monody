<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class InGame implements Rule, DataAwareRule
{
    /**
     * @var string[]
     */
    private array $data;

    /**
     * @param ?string $userIdField The validation field that contains the userId, if null, it's the current field
     */
    public function __construct(private readonly ?string $userIdField = 'userId')
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $gameId = 'gameId' === $attribute ? $value : $this->data['gameId'];

        if (!Redis::exists("game:$gameId")) {
            return false;
        }

        if (null === $this->userIdField) {
            $userId = Auth::user()?->getAuthIdentifier();
        } else {
            $userId = $attribute === $this->userIdField ? $value : $this->data[$this->userIdField];
        }

        $game = json_decode(Redis::get("game:{$gameId}"), true);

        return \in_array($userId, $game['users'], true);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'User is not in the game with given id';
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
