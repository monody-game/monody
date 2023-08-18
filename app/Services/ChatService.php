<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\Team;
use App\Events\MessageSent;
use App\Facades\Redis;
use App\Models\Message;
use App\Models\User;
use App\Traits\MemberHelperTrait;

class ChatService
{
    use MemberHelperTrait;

    public function private(string $content, User $author, string $type, string $gameId, array $recievers): void
    {
        $message = new Message([
            'gameId' => $gameId,
            'author' => $this->getAuthor($author),
            'content' => $content,
            'type' => $type,
        ]);

        broadcast(new MessageSent($message, true, $recievers));
    }

    public function send(array $data, User $user): void
    {
        $message = new Message($data);
        $message->set('author', $this->getAuthor($user));
        $message->set('type', 'message');

        MessageSent::dispatch($message);
    }

    public function alert(string $content, string $type, string $gameId, array $recievers = null): void
    {
        $message = new Message([
            'gameId' => $gameId,
            'author' => null,
            'content' => $content,
            'type' => $type,
        ]);

        if ($recievers !== null) {
            broadcast(new MessageSent($message, true, $recievers));

            return;
        }

        broadcast(new MessageSent($message));
    }

    public function werewolf(array $data, User $user): void
    {
        $game = Redis::get("game:{$data['gameId']}");
        $message = new Message($data);
        $message->set('author', $this->getAuthor($user));
        $message->set('type', 'werewolf');

        broadcast(new MessageSent($message, true, [
            ...$this->getUsersByTeam(Team::Werewolves, $data['gameId']),
            ...array_keys($game['dead_users']),
        ]));

        /** @var array{}|string[] $littleGirl */
        $littleGirl = $this->getUserIdByRole(Role::LittleGirl, $data['gameId']);

        if ($littleGirl !== []) {
            $message->set('author', [
                'id' => '',
                'username' => __('game.werewolf'),
                'avatar' => '/assets/roles/werewolf.png',
            ]);

            broadcast(new MessageSent($message, true, [
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
