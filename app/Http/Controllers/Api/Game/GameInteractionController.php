<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Enums\Roles;
use App\Enums\Teams;
use App\Http\Controllers\Controller;
use App\Http\Requests\CloseInteractionRequest;
use App\Http\Requests\CreateInteractionRequest;
use App\Http\Requests\InteractionRequest;
use App\Services\InteractionService;
use App\Traits\InteractsWithRedis;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GameInteractionController extends Controller
{
    use MemberHelperTrait, InteractsWithRedis;

    public function __construct(
        private readonly InteractionService $service
    ) {
    }

    public function create(CreateInteractionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $type = Interactions::from($validated['type']);
        $gameId = $validated['gameId'];

        $interaction = $this->service->create(
            $gameId,
            $type,
            $this->getAuthorizedMembersByType($type, $gameId)
        );

        return new JsonResponse([
            'interaction' => $interaction,
        ]);
    }

    public function close(CloseInteractionRequest $request): JsonResponse
    {
        $errors = $this->service->close(...$request->safe(['gameId', 'id']));

        if ($errors) {
            switch ($errors) {
                case $this->service::INTERACTION_DOES_NOT_EXISTS:
                    return (new JsonResponse([], Response::HTTP_NOT_FOUND))
                        ->withMessage("Given interaction with id {$request->validated('id')} does not exists");
                case $this->service::NOT_ANY_INTERACTION_STARTED:
                    return (new JsonResponse([], Response::HTTP_NOT_FOUND))
                        ->withMessage("Not any interaction started for game with id {$request->validated('gameId')}");
            }
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function interact(InteractionRequest $request): JsonResponse
    {
        /** @var string $userId */
        $userId = $request->user()?->id;
        $action = $request->validated('action');
        $gameId = $request->validated('gameId');
        $id = $request->validated('id');
        $targetId = $request->validated('targetId', '');
        $deaths = $this->redis()->get("game:$gameId:deaths") ?? [];

        if (!$this->alive($userId, $gameId) && array_filter($deaths, fn ($death) => $death['user'] === $userId) === []) {
            return (new JsonResponse([], Response::HTTP_FORBIDDEN))
                ->withMessage('You are not alive.');
        }

        $action = InteractionActions::from($action);
        $result = $this->service->call($action, $id, $userId, $targetId);

        return match ($result) {
            $this->service::USER_CANNOT_USE_THIS_INTERACTION => (new JsonResponse([], Response::HTTP_FORBIDDEN))
                ->withMessage("You cannot use the action $action->value."),

            $this->service::INVALID_ACTION_ON_INTERACTION => (new JsonResponse([], Response::HTTP_BAD_REQUEST))
                ->withMessage("You cannot use the action $action->value on the interaction $id."),

            $this->service::INTERACTION_DOES_NOT_EXISTS => (new JsonResponse([], Response::HTTP_BAD_REQUEST))
                ->withMessage("Interaction {$id} does not exist."),

            default => new JsonResponse([
                'action' => $action->value,
                'id' => $id,
                'response' => $result,
            ], Response::HTTP_OK),
        };
    }

    public function status(CreateInteractionRequest $request): JsonResponse
    {
        return new JsonResponse($this->service->status($request->validated('gameId'), $request->validated('type')));
    }

    private function getAuthorizedMembersByType(Interactions $type, string $gameId): string|array
    {
        return match ($type) {
            Interactions::Witch => $this->getUserIdByRole(Roles::Witch, $gameId),
            Interactions::Psychic => $this->getUserIdByRole(Roles::Psychic, $gameId),
            Interactions::Werewolves => $this->getUsersByTeam(Teams::Werewolves, $gameId),
            Interactions::InfectedWerewolf => $this->getUserIdByRole(Roles::InfectedWerewolf, $gameId),
            Interactions::WhiteWerewolf => $this->getUserIdByRole(Roles::WhiteWerewolf, $gameId),
            Interactions::Angel => $this->getUserIdByRole(Roles::Angel, $gameId),
            default => '*'
        };
    }
}
