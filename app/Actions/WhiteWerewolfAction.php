<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\State;
use App\Services\VoteService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class WhiteWerewolfAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function __construct(
        private readonly VoteService $service)
    {
    }

    public function isSingleUse(): bool
    {
        return false;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);

        return $this->getUserIdByRole(Role::WhiteWerewolf, $gameId)[0] === $userId && $this->alive($targetId, $gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): bool
    {
        return $this->service->kill($targetId, $this->getGameId($targetId), State::WhiteWerewolf->stringify());
    }

    public function updateClients(string $userId): void
    {
    }

    public function additionnalData(string $gameId): null
    {
        return null;
    }

    public function close(string $gameId): void
    {
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    public function status(string $gameId): null
    {
        return null;
    }
}
