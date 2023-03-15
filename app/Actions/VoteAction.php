<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Events\InteractionUpdate;
use App\Services\VoteService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class VoteAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function __construct(
        private readonly VoteService $service
    ) {
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return $this->alive($targetId, $this->getGameId($targetId));
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): mixed
    {
        return $this->service->vote($targetId, $this->getGameId($targetId), $emitterId);
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    public function updateClients(string $userId): void
    {
        $gameId = $this->getGameId($userId);
        broadcast(new InteractionUpdate([
            'gameId' => $gameId,
            'type' => InteractionAction::Vote->value,
            'votedPlayers' => $this->service::getVotes($gameId),
        ]));
    }

    public function close(string $gameId): void
    {
        $this->service->afterVote($gameId);
    }

    public function isSingleUse(): bool
    {
        return false;
    }

    public function additionnalData(string $gameId): null
    {
        return null;
    }

    public function status(string $gameId): null
    {
        return null;
    }
}
