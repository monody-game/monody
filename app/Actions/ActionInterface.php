<?php

namespace App\Actions;

use App\Enums\InteractionActions;

interface ActionInterface
{
    public function canInteract(string $userId): bool;

    public function call(string $targetId, InteractionActions $action): mixed;
}
