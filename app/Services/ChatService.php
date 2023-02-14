<?php

namespace App\Services;

use App\Enums\Roles;
use App\Enums\Teams;
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
        $message->set('type', 'message');

        MessageSended::dispatch($message);
    }

    public function alert(string $content, string $type, string $gameId, array|null $recievers = null): void
    {
        $message = new Message([
            'gameId' => $gameId,
            'author' => null,
            'content' => $content,
            'type' => $type,
        ]);

        if ($recievers !== null) {
            broadcast(new MessageSended($message, true, [
                ...$recievers,
            ]));

            return;
        }

        broadcast(new MessageSended($message));
    }

    public function werewolf(array $data, User $user): void
    {
        $message = new Message($data);
        $message->set('author', $this->getAuthor($user));
        $message->set('type', 'werewolf');

        broadcast(new MessageSended($message, true, [
            ...$this->getUsersByTeam(Teams::Werewolves, $data['gameId']),
        ]));

        /** @var array{}|string[] $littleGirl */
        $littleGirl = $this->getUserIdByRole(Roles::LittleGirl, $data['gameId']);

        if ($littleGirl !== []) {
            $message->set('author', [
                'id' => '',
                'username' => 'Loup-garou',
                'avatar' => '/assets/roles/werewolf.png',
            ]);

            broadcast(new MessageSended($message, true, [
                $littleGirl[0],
            ]));
        }
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
