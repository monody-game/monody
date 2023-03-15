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
        private readonly VoteService $service
    ) {
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);

        return in_array($userId, $this->getUsersByTeam(Team::Werewolves, $gameId), true) && $this->alive($targetId, $gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): mixed
    {
        return $this->service->vote($targetId, $this->getGameId($targetId));
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    public function updateClients(string $userId): void
    {
        $gameId = $this->getGameId($userId);
        $game = Redis::get("game:$gameId");

        broadcast(new InteractionUpdate([
            'gameId' => $gameId,
            'type' => InteractionAction::Kill->value,
            'votedPlayers' => $this->service::getVotes($gameId),
        ], true, [...$this->getUsersByTeam(Team::Werewolves, $gameId), ...$game['dead_users']]));
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
