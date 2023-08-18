<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\State;
use App\Traits\MemberHelperTrait;

class WhiteWerewolfAction implements ActionInterface
{
    use MemberHelperTrait;

    public function __construct(
        private readonly string $gameId
    ) {
    }

    public function isSingleUse(): bool
    {
        return true;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        return $this->getUserIdByRole(Role::WhiteWerewolf, $this->gameId)[0] === $userId && $this->alive($targetId, $this->gameId);
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): bool
    {
        return $this->kill($targetId, $this->gameId, State::WhiteWerewolf->stringify());
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
