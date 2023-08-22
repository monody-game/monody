<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\InteractionUpdate;
use App\Facades\Redis;
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
        return !$this->canCompare;
    }

    /**
     * {@inheritDoc}
     */
    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return
            $this->alive($targetId, $this->gameId) &&
            $this->getRoleByUserId($userId, $this->gameId) === Role::Investigator &&
            $this->canCompare($userId, $targetId);
    }

    private function canCompare(string $investigator, string $target): bool
    {
        $compared = Redis::get("game:$this->gameId:interactions:investigator") ?? [];

        if ($investigator === $target) {
            return !in_array($target, $compared, true);
        }

        return count(array_filter($compared, fn ($user) => $user === $target)) < 2;
    }

    /**
     * {@inheritDoc}
     */
    public function call(string $targetId, InteractionAction $action, string $emitterId): ?bool
    {
        $votes = array_values($this->service::getVotes($this->gameId));
        $votes = count($votes) === 0 ? [] : $votes[0];

        $this->canCompare = (count($votes) + 1) < 2;

        // Emitter and target switched place in order to allow investigator to vote multiple players
        // We just have to take this in count in every places we use investigator's votes
        $this->service->vote($emitterId, $this->gameId, $targetId);

        Redis::update("game:$this->gameId:interactions:investigator", function (&$compared) use ($targetId) {
            $compared[] = $targetId;
        });

        if ($this->canCompare) {
            return null;
        }

        return $this->service->compare($this->gameId);
    }

    /**
     * {@inheritDoc}
     */
    public function updateClients(string $userId): void
    {
        // Give result of comparaison only if it is necessary
        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Compare->value,
            //'comparedPlayers' => [], // provide compared players, maybe through VoteService
        ]));
    }

    /**
     * {@inheritDoc}
     */
    public function additionnalData(): array
    {
        return [
            'not_comparable' => $this->getNotComparableUsers(),
        ];
    }

    private function getNotComparableUsers(): array
    {
        $compared = Redis::get("game:$this->gameId:interactions:investigator") ?? [];
        $investigator = $this->getUserIdByRole(Role::Investigator, $this->gameId)[0];

        $compared = array_filter(
            $compared,
            fn ($comparedUser) => $comparedUser === $investigator ||
                count(array_filter($compared, fn ($user) => $user === $comparedUser)) === 2
        );

        return array_unique($compared);
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->service->clearVotes($this->gameId);
    }

    /**
     * {@inheritDoc}
     */
    public function status(): null
    {
        return null;
    }
}
