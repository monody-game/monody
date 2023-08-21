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
        $canPair = !array_key_exists($emitterId, $toPair) || count($toPair[$emitterId]) < 2;

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
    public function updateClients(string $userId): void
    {
        $game = Redis::get("game:$this->gameId");
        $couple = array_key_exists('couple', $game) ? [$userId => $game['couple']] : $this->service::getVotes($this->gameId);

        broadcast(new InteractionUpdate([
            'gameId' => $this->gameId,
            'type' => InteractionAction::Pair->value,
            'votedPlayers' => $couple,
        ], true, [$userId]));
    }

    /**
     * {@inheritDoc}
     */
    public function additionnalData(): null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->service->clearVotes($this->gameId);
        $game = Redis::get("game:$this->gameId");

        // If couple wasn't designed, take it randomly
        if (!array_key_exists('couple', $game)) {
            $game['couple'] = [];
            for ($i = 0; $i < 2; $i++) {
                $userIndex = array_rand($game['users']);

                while (in_array($game['users'][$userIndex], $game['couple'], true)) {
                    $userIndex = array_rand($game['users']);
                }

                $game['couple'][] = $game['users'][$userIndex];
            }

            Redis::set("game:$this->gameId", $game);

            broadcast(new CouplePaired(
                payload: [
                    'gameId' => $this->gameId,
                    'type' => InteractionAction::Pair->value,
                    'pairedPlayers' => $game['couple'],
                ],
                recipients: [...$game['couple'], $this->getUserIdByRole(Role::Cupid, $this->gameId)[0]]
            ));

            Redis::update("game:$this->gameId:interactions:usedActions", function (&$usedActions) {
                $usedActions[] = Role::Cupid->name();
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function status(): null
    {
        return null;
    }
}
