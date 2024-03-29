<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Events\InteractionUpdate;
use App\Services\Vote\MayorService;
use App\Traits\MemberHelperTrait;

class MayorAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private readonly MayorService $service,
        private readonly string $gameId
    ) {
    }

    public function isSingleUse(): bool
    {
        return false;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return $this->alive($targetId, $this->gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): array
    {
        return $this->service->vote($targetId, $this->gameId, $emitterId);
    }

    public function updateClients(string $userId): void
    {
        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Vote->value,
            'votedPlayers' => $this->service::getVotes($this->gameId),
        ]));
    }

    public function additionnalData(): null
    {
        return null;
    }

    public function close(): void
    {
        $this->service->elect($this->gameId);
    }

    public function status(): null
    {
        return null;
    }
}
