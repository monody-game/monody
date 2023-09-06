<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Events\InteractionUpdate;
use App\Events\Websockets\TimeSkip;
use App\Services\Vote\SkipVoteService;
use App\Traits\MemberHelperTrait;

class SkipAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private readonly string $gameId,
        private readonly SkipVoteService $service
    ) {
    }

    public function isSingleUse(): bool
    {
        return false;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return $this->alive($userId, $this->gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): mixed
    {
        $this->service->vote('time_skip', $this->gameId, $emitterId);

        if ($this->service->skip($this->gameId)) {
            $this->service->clearVotes($this->gameId);
            broadcast(new TimeSkip($this->gameId, 0));
        }

        return null;
    }

    public function updateClients(string $userId): void
    {
        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Skip->value,
            'votingPlayers' => $this->service::getVotes($this->gameId),
        ]));
    }

    public function additionnalData(): null
    {
        return null;
    }

    public function close(): void
    {
        $this->service->clearVotes($this->gameId);
    }

    public function status(): null
    {
        return null;
    }
}
