<?php

namespace App\Actions;

use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Events\WerewolvesList;
use App\Services\ChatService;
use App\Traits\InteractsWithRedis;
use App\Traits\MemberHelperTrait;
use App\Traits\RegisterHelperTrait;

class InfectedWerewolfAction implements ActionInterface
{
    use MemberHelperTrait, RegisterHelperTrait, InteractsWithRedis;

    public function isSingleUse(): bool
    {
        return true;
    }

    public function canInteract(InteractionActions $action, string $userId, string $targetId = ''): bool
    {
        $gameId = $this->getGameId($userId);
        $actionCondition = true;

        if ($action === InteractionActions::Infect) {
            $target = $this->getMember($targetId, $gameId);

            if (!$target) {
                $actionCondition = false;
            } else {
                $deaths = $this->redis()->get("game:$gameId:deaths");

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

        return $role === Roles::InfectedWerewolf && $actionCondition;
    }

    public function call(string $targetId, InteractionActions $action, string $emitterId): null
    {
        $gameId = $this->getGameId($targetId);
        switch ($action) {
            case InteractionActions::InfectedSkip:
                return null;
            case InteractionActions::Infect:
                $this->infection($targetId);
                break;
        }

        $this->setUsed(InteractionActions::Infect, $gameId);

        return null;
    }

    public function infection(string $targetId): void
    {
        $gameId = $this->getGameId($targetId);
        $game = $this->redis()->get("game:$gameId");
        $deaths = $this->redis()->get("game:$gameId:deaths") ?? [];

        if ($this->isUsed(InteractionActions::Infect, $gameId)) {
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

        $this->redis()->set("game:$gameId:deaths", $deaths);
        $this->redis()->set("game:$gameId", $game);
    }

    public function updateClients(string $userId): void
    {
    }

    public function additionnalData(string $gameId): mixed
    {
        $deaths = $this->redis()->get("game:$gameId:deaths") ?? [];

        return array_map(fn ($death) => $death['user'], $deaths);
    }

    public function close(string $gameId): void
    {
    }

    private function getGameId(string $userId): string
    {
        return $this->getCurrentUserGameActivity($userId);
    }

    private function getRole(string $userId): Roles
    {
        return $this->getRoleByUserId($userId, $this->getGameId($userId));
    }

    private function setUsed(InteractionActions $action, string $gameId): void
    {
        $usedActions = $this->redis()->get("game:$gameId:interactions:usedActions") ?? [];
        $usedActions[] = $action->value;

        $this->redis()->set("game:$gameId:interactions:usedActions", $usedActions);
    }

    private function isUsed(InteractionActions $action, string $gameId): bool
    {
        $usedActions = $this->redis()->get("game:$gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }

    public function status(string $gameId): null
    {
        return null;
    }
}
