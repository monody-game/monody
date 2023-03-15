<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\WerewolvesList;
use App\Facades\Redis;
use App\Services\ChatService;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class InfectedWerewolfAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait;

    public function isSingleUse(): bool
    {
        return true;
    }

    public function canInteract(InteractionAction $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);
        $actionCondition = true;

        if ($action === InteractionAction::Infect) {
            $target = $this->getMember($targetId, $gameId);

            if (!$target) {
                $actionCondition = false;
            } else {
                $deaths = Redis::get("game:$gameId:deaths");

                if (
                    array_key_exists('is_dead', $target['user_info']) &&
                    $target['user_info']['is_dead'] === false &&
                    !in_array($targetId, array_map(fn ($death) => $death['user'], $deaths), true)
                ) {
                    $actionCondition = false;
                }
            }
        }

        $role = $this->getRole($userId);

        return $role === Role::InfectedWerewolf && $actionCondition;
    }

    public function call(string $targetId, InteractionAction $action, string $emitterId): null
    {
        $gameId = $this->getGameId($targetId);
        switch ($action) {
            case InteractionAction::InfectedSkip:
                return null;
            case InteractionAction::Infect:
                $this->infection($targetId);
                break;
        }

        $this->setUsed(InteractionAction::Infect, $gameId);

        return null;
    }

    public function infection(string $targetId): void
    {
        $gameId = $this->getGameId($targetId);
        $game = Redis::get("game:$gameId");
        $deaths = Redis::get("game:$gameId:deaths") ?? [];

        if ($this->isUsed(InteractionAction::Infect, $gameId)) {
            return;
        }

        if (!array_key_exists('dead_users', $game) && in_array($targetId, $game['dead_users'], true)) {
            return;
        }

        $index = array_search($targetId, $game['dead_users'], true);
        array_splice($game['dead_users'], (int) $index, 1);
        $deaths = array_filter($deaths, fn ($death) => $death['user'] !== $targetId);

        $game['werewolves'][] = $targetId;
        $game['infected'] = $targetId;

        $chat = new ChatService();
        $chat->alert('Vous avez été infecté ! Vous devez désormais gagner avec les loup-garous', 'info', $gameId, [$targetId]);

        broadcast(
            new WerewolvesList(
                [
                    'gameId' => $gameId,
                    'list' => $game['werewolves'],
                ],
                true,
                [...$game['werewolves'], ...$game['dead_users']]
            )
        );

        Redis::set("game:$gameId:deaths", $deaths);
        Redis::set("game:$gameId", $game);
    }

    public function updateClients(string $userId): void
    {
    }

    public function additionnalData(string $gameId): mixed
    {
        $deaths = Redis::get("game:$gameId:deaths") ?? [];

        return array_map(fn ($death) => $death['user'], $deaths);
    }

    public function close(string $gameId): void
    {
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    private function getRole(string $userId): Role
    {
        return $this->getRoleByUserId($userId, $this->getGameId($userId));
    }

    private function setUsed(InteractionAction $action, string $gameId): void
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];
        $usedActions[] = $action->value;

        Redis::set("game:$gameId:interactions:usedActions", $usedActions);
    }

    private function isUsed(InteractionAction $action, string $gameId): bool
    {
        $usedActions = Redis::get("game:$gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }

    public function status(string $gameId): null
    {
        return null;
    }
}
