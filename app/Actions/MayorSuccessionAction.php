<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Events\MayorElected;
use App\Facades\Redis;
use App\Traits\MemberHelperTrait;

class MayorSuccessionAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private string $gameId
    ) {
    }

    public function isSingleUse(): bool
    {
        return true;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $game = Redis::get("game:$this->gameId");

        return
            array_key_exists('mayor', $game) &&
            $game['mayor'] === $userId &&
            $this->alive($targetId, $this->gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): null
    {
        Redis::update("game:$this->gameId", fn (array &$game) => $game['mayor'] = $targetId);

        broadcast(new MayorElected([
            'gameId' => $this->gameId,
            'mayor' => $targetId,
        ]));

        return null;
    }

    public function updateClients(string $userId): void
    {
    }

    public function additionnalData(): null
    {
        return null;
    }

    public function close(): void
    {
    }

    public function status(): null
    {
        return null;
    }
}
