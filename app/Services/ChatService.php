<?php

namespace App\Services;

use App\Events\MessageSended;
use App\Models\Message;
use App\Models\User;
use App\Traits\MemberHelperTrait;

class ChatService
{
    use MemberHelperTrait;

    public function send(array $data, User $user): void
    {
        $message = new Message($data);
        $message->set('author', $this->getAuthor($user));

        MessageSended::dispatch($message);
    }

    public function werewolf(array $data, User $user): void
    {
        $message = new Message($data);
        $message->set('author', $this->getAuthor($user));
        $message->set('type', 'werewolf');

        broadcast(new MessageSended($message, true, [
            $this->getWerewolves($data['gameId'])[0],
        ]));
    }

    private function getAuthor(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'avatar' => $user->avatar,
        ];
    }
}
