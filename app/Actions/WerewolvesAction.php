<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\State;
use App\Enums\Team;
use App\Events\InteractionUpdate;
use App\Facades\Redis;
use App\Services\VoteService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class WerewolvesAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function __construct(
        private readonly VoteService $service,
        private readonly string $gameId
    ) {
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return in_array($userId, $this->getUsersByTeam(Team::Werewolves, $this->gameId), true) && $this->alive($targetId, $this->gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): array
    {
        return $this->service->vote($targetId, $this->gameId);
    }

    public function updateClients(string $userId): void
    {
        $game = Redis::get("game:$this->gameId");

        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Kill->value,
            'votedPlayers' => $this->service::getVotes($this->gameId),
        ], true, [...$this->getUsersByTeam(Team::Werewolves, $this->gameId), ...array_keys($game['dead_users'])]));
    }

    public function close(string $gameId): void
    {
        $this->service->afterVote($gameId, State::Werewolf->stringify());
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
