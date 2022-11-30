<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\States;
use App\Events\ChatLock;
use App\Events\GameKill;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameIdRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\User;
use App\Services\ChatService;
use App\Traits\GameHelperTrait;
use App\Traits\MemberHelperTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GameChatController extends Controller
{
    use GameHelperTrait, MemberHelperTrait;

    private ChatService $service;

    public function __construct()
    {
        $this->service = new ChatService();
    }

    public function send(SendMessageRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');
        /** @var User $user */
        $user = $request->user();
        $state = $this->getState($gameId)['state'];

        if ($state === States::Werewolf->value && $this->isWerewolf($user['id'], $gameId)) {
            $this->service->werewolf($request->validated(), $user);
        }

        $this->service->send($request->validated(), $user);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function death(GameIdRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');
        $deaths = Redis::get("game:{$gameId}:deaths") ?? [];

        foreach ($deaths as $death) {
            GameKill::broadcast([
                'killedUser' => $death['user'],
                'gameId' => $gameId,
                'context' => $death['context'],
            ]);
        }

        Redis::set("game:{$gameId}:deaths", []);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function lock(GameIdRequest $request): JsonResponse
    {
        broadcast(new ChatLock($request->validated('gameId')));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
