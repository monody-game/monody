<?php

namespace App\Actions;

use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\WerewolvesList;
use App\Facades\Redis;
use App\Services\ChatService;
use App\Traits\MemberHelperTrait;

class InfectedWerewolfAction implements ActionInterface
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
        $actionCondition = true;

        if ($action === InteractionAction::Infect) {
            $target = $this->getMember($targetId, $this->gameId);

            if (!$target) {
                $actionCondition = false;
            } else {
                $deaths = Redis::get("game:$this->gameId:deaths");

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
        switch ($action) {
            case InteractionAction::InfectedSkip:
                return null;
            case InteractionAction::Infect:
                $this->infection($targetId);
                break;
        }

        $this->setUsed(InteractionAction::Infect);

        return null;
    }

    public function infection(string $targetId): void
    {
        $game = Redis::get("game:$this->gameId");
        $deaths = Redis::get("game:$this->gameId:deaths") ?? [];

        if ($this->isUsed(InteractionAction::Infect)) {
            return;
        }

        if (!array_key_exists('dead_users', $game) && in_array($targetId, array_keys($game['dead_users']), true)) {
            return;
        }

        $index = array_search($targetId, array_keys($game['dead_users']), true);
        array_splice($game['dead_users'], (int) $index, 1);
        $deaths = array_filter($deaths, fn ($death) => $death['user'] !== $targetId);

        $game['werewolves'][] = $targetId;
        $game['infected'] = $targetId;

        $chat = new ChatService();
        $chat->alert(__('game.infected'), 'info', $this->gameId, [$targetId]);

        broadcast(
            new WerewolvesList(
                [
                    'gameId' => $this->gameId,
                    'list' => $game['werewolves'],
                ],
                true,
                [...$game['werewolves'], ...array_keys($game['dead_users'])]
            )
        );

        Redis::set("game:$this->gameId:deaths", $deaths);
        Redis::set("game:$this->gameId", $game);
    }

    public function updateClients(string $userId): void
    {
    }

    public function additionnalData(): array
    {
        $deaths = Redis::get("game:$this->gameId:deaths") ?? [];

        return array_map(fn ($death) => $death['user'], $deaths);
    }

    public function close(): void
    {
    }

    private function getRole(string $userId): Role
    {
        return $this->getRoleByUserId($userId, $this->gameId);
    }

    private function setUsed(InteractionAction $action): void
    {
        Redis::update("game:$this->gameId:interactions:usedActions", function (array &$usedActions) use ($action) {
            $usedActions[] = $action->value;
        });
    }

    private function isUsed(InteractionAction $action): bool
    {
        $usedActions = Redis::get("game:$this->gameId:interactions:usedActions") ?? [];

        return in_array($action->value, $usedActions, true);
    }

    public function status(): null
    {
        return null;
    }
}
