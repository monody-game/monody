<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\CloseInteractionRequest;
use App\Http\Requests\CreateInteractionRequest;
use App\Http\Requests\InteractionRequest;
use App\Services\InteractionService;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GameInteractionController extends Controller
{
    use MemberHelperTrait;

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
        $errors = $this->service->close(...$request->safe(['gameId', 'interactionId']));

        if ($errors) {
            switch ($errors) {
                case $this->service::INTERACTION_DOES_NOT_EXISTS:
                    return new JsonResponse([
                        'message' => "Given interaction with id {$request->validated('interactionId')} does not exists",
                    ],
                        Response::HTTP_NOT_FOUND
                    );
                case $this->service::NOT_ANY_INTERACTION_STARTED:
                    return new JsonResponse([
                        'message' => "Not any interaction started for game with id {$request->validated('gameId')}",
                    ],
                        Response::HTTP_NOT_FOUND
                    );
            }
        }

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    public function interact(InteractionRequest $request): JsonResponse
    {
        /** @var string $userId */
        $userId = $request->user()?->id;
        $interaction = $request->validated('interaction');
        $gameId = $request->validated('gameId');

        if (!$this->alive($userId, $gameId)) {
            return new JsonResponse([
                'message' => 'You are not alive',
            ], Response::HTTP_FORBIDDEN);
        }

        $action = InteractionActions::from($interaction);
        $result = $this->service->call($action, $userId, $request->validated('targetId'));

        if ($result === $this->service::USER_CANNOT_USE_THIS_INTERACTION) {
            return new JsonResponse([
                'message' => "You can't use the interaction {$interaction}",
            ], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse([
            'interaction' => $action->value,
            'interactionId' => $request->validated('interactionId'),
            'response' => $result,
        ], Response::HTTP_OK);
    }

    private function getAuthorizedMembersByType(Interactions $type, string $gameId): string|array
    {
        $authorized = '*';

        if ($type === Interactions::Witch) {
            $authorized = $this->getUserIdByRole(Roles::Witch->value, $gameId);
        } elseif ($type === Interactions::Psychic) {
            $authorized = $this->getUserIdByRole(Roles::Psychic->value, $gameId);
        } elseif ($type === Interactions::Werewolves) {
            $authorized = $this->getWerewolves($gameId);
        }

        if ($authorized === false) {
            return '*';
        }

        if (is_array($authorized) && count($authorized) === 1) {
            $authorized = $authorized[0];
        }

        return $authorized;
    }
}