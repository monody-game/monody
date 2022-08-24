<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\GameInteractions;
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
        $type = GameInteractions::from($validated['type']);
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
        return new JsonResponse('', Response::HTTP_NOT_IMPLEMENTED);
    }

    private function getAuthorizedMembersByType(GameInteractions $type, string $gameId): string|array
    {
        $authorized = '*';

        if ($type === GameInteractions::Witch) {
            $authorized = $this->getUserIdByRole(Roles::Witch->value, $gameId);
        } elseif ($type === GameInteractions::Psychic) {
            $authorized = $this->getUserIdByRole(Roles::Psychic->value, $gameId);
        } elseif ($type === GameInteractions::Werewolves) {
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
