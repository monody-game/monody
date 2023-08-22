<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\InteractionUpdate;
use App\Services\InvestigatorService;
use App\Traits\MemberHelperTrait;

class InvestigatorAction implements ActionInterface
{
    use MemberHelperTrait;

    private bool $canCompare = true;

    public function __construct(
        private readonly string $gameId,
        private readonly InvestigatorService $service
    ) {
    }

    public function isSingleUse(): bool
    {
        return $this->canCompare;
    }

    /**
     * {@inheritDoc}
     */
    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return
            $this->alive($targetId, $this->gameId) &&
            $this->getRoleByUserId($userId, $this->gameId) === Role::Investigator;
    }

    /**
     * {@inheritDoc}
     */
    public function call(string $targetId, InteractionAction $action, string $emitterId): array
    {
		$votes = array_values($this->service::getVotes($this->gameId))[0];
        $this->canCompare = (count($votes) + 1) === 2;

        return $this->service->vote($targetId, $this->gameId, $emitterId);
    }

    /**
     * {@inheritDoc}
     */
    public function updateClients(string $userId): void
    {
        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Compare->value,
            'comparedPlayers' => [], // provide compared players, maybe through VoteService
        ]));
    }

    /**
     * {@inheritDoc}
     */
    public function additionnalData(): null
    {
        // TODO: return which players can the investigator compare and which he cannot
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        // TODO: Implement close() method.
    }

    /**
     * {@inheritDoc}
     */
    public function status(): null
    {
        return null;
    }
}
