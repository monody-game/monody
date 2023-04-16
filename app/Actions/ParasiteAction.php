<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\InteractionUpdate;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;

class ParasiteAction implements ActionInterface
{
    use MemberHelperTrait;

    private bool $canContaminate = true;

    public function __construct(
        private readonly string $gameId
    ) {
    }

    public function isSingleUse(): bool
    {
        return $this->canContaminate;
    }

    /**
     * {@inheritDoc}
     */
    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return
            $this->alive($targetId, $this->gameId) &&
            $this->getRoleByUserId($userId, $this->gameId) === Role::Parasite;
    }

    /**
     * {@inheritDoc}
     */
    public function call(string $targetId, InteractionAction $action, string $emitterId): null
    {
        $game = Redis::get("game:$this->gameId");
        $state = Redis::get("game:$this->gameId:state");
        $toContaminate = $this->additionnalData($this->gameId);
        $contaminated = array_key_exists('contaminated', $game) ? $game['contaminated'] : [];

        $contaminated[] = $targetId;

        // Detect if the parasite can contaminate other players during this interaction
        if (
            count($contaminated) - ($toContaminate * ($state['round'] + 1)) >= $toContaminate
        ) {
            $this->canContaminate = false;
        }

        $game['contaminated'] = $contaminated;

        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Contaminate->value,
            'contaminated' => $contaminated,
        ], true, $contaminated));

        Redis::set("game:$this->gameId", $game);

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function updateClients(string $userId): void
    {
    }

    /**
     * Returns the number of players the parasite can contaminate during the interaction.
     * If there is less than 8 players in game, the parasite can only contaminate 1 player per night, 2 otherwise.
     */
    public function additionnalData(string $gameId): int
    {
        $game = Redis::get("game:$gameId");

        return count($game['users']) < 8 ? 1 : 2;
    }

    /**
     * {@inheritDoc}
     */
    public function close(string $gameId): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function status(string $gameId): null
    {
        return null;
    }
}
