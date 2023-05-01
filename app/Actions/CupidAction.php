<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\CouplePaired;
use App\Events\InteractionUpdate;
use App\Facades\Redis;
use App\Services\VoteService;
use App\Traits\MemberHelperTrait;

class CupidAction implements ActionInterface
{
    use MemberHelperTrait;

    private bool $canPair = true;

    public function __construct(
        private readonly VoteService $service,
        private readonly string $gameId
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleUse(): bool
    {
        return !$this->canPair;
    }

    /**
     * {@inheritDoc}
     */
    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return $this->alive($targetId, $this->gameId) &&
            $this->getRoleByUserId($userId, $this->gameId) === Role::Cupid;
    }

    /**
     * {@inheritDoc}
     */
    public function call(string $targetId, InteractionAction $action, string $emitterId): array
    {
        $toPair = $this->service->vote($emitterId, $this->gameId, $targetId);
        $canPair = count($toPair[$emitterId]) < 2;

        $this->canPair = $canPair;

        // If the cupid can't pair another player, then we need to end the interaction and save the couple
        if (!$canPair) {
            $game = Redis::update("game:$this->gameId", function ($game) use ($toPair, $emitterId) {
                $game['couple'] = $toPair[$emitterId];

                return $game;
            });

            broadcast(new CouplePaired(
                payload: [
                    'gameId' => $this->gameId,
                    'type' => InteractionAction::Pair->value,
                    'pairedPlayers' => $game['couple'],
                ],
                recipients: [...$game['couple'], $emitterId]
            ));

            Redis::update("game:$this->gameId:interactions:usedActions", function (&$usedActions) {
                $usedActions[] = Role::Cupid->name();
            });
        }

        return $toPair;
    }

    /**
     * {@inheritDoc}
     */
    public function updateClients(string $emitterId): void
    {
        $game = Redis::get("game:$this->gameId");
        $couple = array_key_exists('couple', $game) ? [$emitterId => $game['couple']] : $this->service::getVotes($this->gameId);

        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Pair->value,
            'votedPlayers' => $couple,
        ], true, [$emitterId]));
    }

    /**
     * {@inheritDoc}
     */
    public function additionnalData(string $gameId): null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function close(string $gameId): void
    {
        $this->service->clearVotes($this->gameId);
    }

    /**
     * {@inheritDoc}
     */
    public function status(string $gameId): null
    {
        return null;
    }
}
