<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\Status;
use App\Enums\Team;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\CloseInteractionRequest;
use App\Http\Requests\CreateInteractionRequest;
use App\Http\Requests\InteractionRequest;
use App\Http\Responses\JsonApiResponse;
use App\Services\InteractionService;
use App\Traits\MemberHelperTrait;

class GameInteractionController extends Controller
{
    use MemberHelperTrait;

    public function __construct(
        private readonly InteractionService $service
    ) {
    }

    public function create(CreateInteractionRequest $request): JsonApiResponse
    {
        $validated = $request->validated();
        $type = Interaction::from($validated['type']);
        $gameId = $validated['gameId'];

        $interaction = $this->service->create(
            $gameId,
            $type,
            $this->getAuthorizedMembersByType($type, $gameId)
        );

        return new JsonApiResponse([
            'interaction' => $interaction,
        ]);
    }

    public function close(CloseInteractionRequest $request): JsonApiResponse
    {
        $errors = $this->service->close(...$request->safe(['gameId', 'id']));

        if ($errors) {
            switch ($errors) {
                case $this->service::INTERACTION_DOES_NOT_EXISTS:
                    return new JsonApiResponse(['message' => "Given interaction with id {$request->validated('id')} does not exists"], Status::NOT_FOUND);
                case $this->service::NOT_ANY_INTERACTION_STARTED:
                    return new JsonApiResponse(['message' => "Not any interaction started for game with id {$request->validated('gameId')}"], Status::NOT_FOUND);
            }
        }

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    public function interact(InteractionRequest $request): JsonApiResponse
    {
        /** @var string $userId */
        $userId = $request->user()?->id;
        $action = $request->validated('action');
        $gameId = $request->validated('gameId');
        $id = $request->validated('id');
        $targetId = $request->validated('targetId', '');
        $deaths = Redis::get("game:$gameId:deaths") ?? [];

        if (!$this->alive($userId, $gameId) && array_filter($deaths, fn ($death) => $death['user'] === $userId) === []) {
            return new JsonApiResponse(['message' => 'You are not alive.'], Status::FORBIDDEN);
        }

        $action = InteractionAction::from($action);
        $result = $this->service->call($action, $id, $userId, $targetId);

        return match ($result) {
            $this->service::USER_CANNOT_USE_THIS_INTERACTION => new JsonApiResponse(['message' => "You cannot use the action $action->value."], Status::FORBIDDEN),
            $this->service::INVALID_ACTION_ON_INTERACTION => new JsonApiResponse(['message' => "You cannot use the action $action->value on the interaction $id."], Status::BAD_REQUEST),
            $this->service::INTERACTION_DOES_NOT_EXISTS => new JsonApiResponse(['message' => "Interaction {$id} does not exist."], Status::BAD_REQUEST),
            default => new JsonApiResponse([
                'interaction' => [
                    'action' => $action->value,
                    'id' => $id,
                    'response' => $result,
                ],
            ]),
        };
    }

    public function status(CreateInteractionRequest $request): JsonApiResponse
    {
        return new JsonApiResponse([
            'status' => $this->service->status($request->validated('gameId'), $request->validated('type')),
        ]);
    }

    private function getAuthorizedMembersByType(Interaction $type, string $gameId): string|array
    {
        return match ($type) {
            Interaction::Witch => $this->getUserIdByRole(Role::Witch, $gameId),
            Interaction::Psychic => $this->getUserIdByRole(Role::Psychic, $gameId),
            Interaction::Werewolves => $this->getUsersByTeam(Team::Werewolves, $gameId),
            Interaction::InfectedWerewolf => $this->getUserIdByRole(Role::InfectedWerewolf, $gameId),
            Interaction::WhiteWerewolf => $this->getUserIdByRole(Role::WhiteWerewolf, $gameId),
            Interaction::Angel => $this->getUserIdByRole(Role::Angel, $gameId),
            Interaction::SurlyWerewolf => $this->getUserIdByRole(Role::SurlyWerewolf, $gameId),
            Interaction::Parasite => $this->getUserIdByRole(Role::Parasite, $gameId),
            Interaction::Cupid => $this->getUserIdByRole(Role::Cupid, $gameId),
            Interaction::Guard => $this->getUserIdByRole(Role::Guard, $gameId),
            default => '*'
        };
    }
}
