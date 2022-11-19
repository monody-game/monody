<?php

namespace App\Actions;

use App\Enums\InteractionActions;

interface ActionInterface
{
    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool;

    public function call(string $targetId, InteractionActions $action): mixed;

    public function updateClients(string $userId): void;

    public function close(string $gameId): void;
}
