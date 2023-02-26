<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Facades\Redis;
use App\Services\EndGameService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class AngelAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function __construct(
        private readonly EndGameService $service
    ) {
    }

    public function isSingleUse(): bool
    {
        return false;
    }

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        return false;
    }

    public function call(string $targetId, InteractionActions $action, string $emitterId): bool
    {
        $gameId = $this->getGameId($emitterId);
        $game = Redis::get("game:$gameId");

        return in_array($game['angel_target'], $game['dead_users'], true);
    }

    public function updateClients(string $userId): void
    {
    }

    /**
     * Define angel's target
     */
    public function additionnalData(string $gameId): string
    {
        $game = Redis::get("game:$gameId");
        $users = array_filter($game['users'], fn ($user) => $user !== $this->getUserIdByRole(Roles::Angel, $gameId)[0]);

        $target = $users[random_int(0, count($users) - 1)];
        $game['angel_target'] = $target;

        Redis::set("game:$gameId", $game);

        return $target;
    }

    /**
     * Check if the target is dead, to dictate if the game needs to be ended
     */
    public function close(string $gameId): void
    {
        $game = Redis::get("game:$gameId");

        if (in_array($game['angel_target'], $game['dead_users'], true)) {
            $this->service->end($gameId, $this->getUserIdByRole(Roles::Angel, $gameId));
        }
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    /**
     * Return true if the angel has won (if his target is dead)
     */
    public function status(string $gameId): bool
    {
        $game = Redis::get("game:$gameId");

        if (in_array($game['angel_target'], $game['dead_users'], true)) {
            return true;
        }

        return false;
    }
}
