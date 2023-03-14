<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\State;
use App\Enums\Team;
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

    public function __construct(
        private readonly ChatService $service
    ) {
    }

    public function send(SendMessageRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');
        $game = $this->getGame($gameId);
        /** @var User $user */
        $user = $request->user();
        $state = $this->getState($gameId)['status'];

        if ($state === State::Werewolf->value && $this->isWerewolf($user['id'], $gameId)) {
            $this->service->werewolf($request->validated(), $user);
        } elseif ($state === State::Werewolf->value && !$this->isWerewolf($user['id'], $gameId)) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        } elseif (!$this->alive($user->id, $gameId)) {
            $this->service->private($request->validated('content'), $user, 'dead', $gameId, $game['dead_users']);
        } else {
            $this->service->send($request->validated(), $user);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function death(GameIdRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');
        $game = Redis::get("game:$gameId");
        $deaths = Redis::get("game:$gameId:deaths") ?? [];

        foreach ($deaths as $death) {
            $infected = array_key_exists('infected', $game) && $game['infected'] === $death['user'];

            GameKill::broadcast([
                'killedUser' => $death['user'],
                'gameId' => $gameId,
                'context' => $death['context'],
                'infected' => $infected,
            ]);
        }

        Redis::set("game:$gameId:deaths", []);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function lock(GameIdRequest $request): JsonResponse
    {
        $gameId = $request->validated('gameId');

        if ($request->has('users')) {
            broadcast(new ChatLock($gameId, true, (array) $request->get('users')));
        } elseif ($request->has('team')) {
            $recipients = [];

            foreach (Team::from($request->get('team'))->roles() as $role) {
                $users = $this->getUserIdByRole($role, $gameId);

                if ($users === []) {
                    continue;
                }

                $recipients = [...$recipients, ...$users];
            }

            broadcast(new ChatLock($gameId, true, $recipients));
        } else {
            broadcast(new ChatLock($gameId));
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
