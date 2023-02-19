<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Events\InteractionUpdate;
use App\Services\VoteService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class MayorAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    private readonly VoteService $service;

    public function __construct()
    {
        $this->service = new VoteService();
    }

    public function isSingleUse(): bool
    {
        return false;
    }

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);

        return $this->alive($targetId, $gameId);
    }

    public function call(string $targetId, InteractionActions $action, string $emitterId): mixed
    {
        return $this->service->vote($targetId, $this->getGameId($targetId), $emitterId);
    }

    public function updateClients(string $userId): void
    {
        $gameId = $this->getGameId($userId);
        broadcast(new InteractionUpdate([
            'gameId' => $gameId,
            'type' => InteractionActions::Vote->value,
            'votedPlayers' => $this->service::getVotes($gameId),
        ]));
    }

    public function additionnalData(string $gameId): null
    {
        return null;
    }

    public function close(string $gameId): void
    {
        $this->service->elect($gameId);
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }
}
