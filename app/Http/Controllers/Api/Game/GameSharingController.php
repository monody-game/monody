<?php

namespace App\Http\Controllers\Api\Game;

use App\Events\GameShare;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\RegisterHelperTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GameSharingController extends Controller
{
    use RegisterHelperTrait, GameHelperTrait;

    public function index(Request $request): JsonResponse
    {
		/** @var User $user endpoint guarded by api guard */
		$user = $request->user();

        $gameId = $this->getCurrentUserGameActivity($user->id);
        $sharedGames = Redis::get('bot:game:shared') ?? [];

        if (in_array($gameId, array_keys($sharedGames), true)) {
            return (new JsonResponse([], Response::HTTP_BAD_REQUEST))
                ->withMessage("The game $gameId has already been shared");
        }

        $game = $this->getGame($gameId);

        broadcast(new GameShare($game));

        return (new JsonResponse())
                ->withMessage('Game successfully shared');
    }
}
