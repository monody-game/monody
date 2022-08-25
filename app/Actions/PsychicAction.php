<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;
use Exception;

class PsychicAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function canInteract(string $userId): bool
    {
        $role = $this->getRole($userId);

        return $role !== false && $role === Roles::Psychic;
    }

    public function call(string $targetId, InteractionActions $action): int
    {
        $role = $this->getRole($targetId);

        if ($role === false) {
            throw new Exception('An error occured, try to see if the game is started');
        }

        return $role->value;
    }

    private function getRole(string $userId): Roles|false
    {
        return $this->getRoleByUserId($userId, $this->getCurrentUserGameActivity($userId) ?? '');
    }
}
