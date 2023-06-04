<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Facades\Redis;
use App\Services\EndGameService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class AngelAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function __construct(
        private readonly EndGameService $service,
        private readonly string $gameId
    ) {
    }

    public function isSingleUse(): bool
    {
        return false;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return false;
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): bool
    {
        $game = Redis::get("game:$this->gameId");

        return in_array($game['angel_target'], array_keys($game['dead_users']), true);
    }

    public function updateClients(string $userId): void
    {
    }

    /**
     * Define angel's target
     */
    public function additionnalData(string $gameId): string
    {
        $game = Redis::update("game:$gameId", function (array &$game) use ($gameId) {
            $users = array_filter($game['users'], fn ($user) => $user !== $this->getUserIdByRole(Role::Angel, $gameId)[0]);
            $users = array_values($users); // Cancel array's key preservation
            /** @var int<5, max> $count */
            $count = count($users);

            $target = $users[random_int(0, $count - 1)];
            $game['angel_target'] = $target;
        });

        return $game['angel_target'];
    }

    /**
     * Check if the target is dead, to dictate if the game needs to be ended
     */
    public function close(string $gameId): void
    {
        $game = Redis::get("game:$gameId");

        if (in_array($game['angel_target'], array_keys($game['dead_users']), true)) {
            $this->service->end($gameId, $this->getUserIdByRole(Role::Angel, $gameId));
        }
    }

    /**
     * Return true if the angel has won (if his target is dead)
     */
    public function status(string $gameId): bool
    {
        $game = Redis::get("game:$gameId");

        if (in_array($game['angel_target'], array_keys($game['dead_users']), true) && !in_array($this->getUserIdByRole(Role::Angel, $gameId)[0], $game['dead_users'], true)) {
            return true;
        }

        return false;
    }
}
