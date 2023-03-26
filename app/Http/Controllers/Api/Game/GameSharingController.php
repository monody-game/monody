<?php

namespace App\Http\Controllers\Api\Game;

use App\Enums\Status;
use App\Events\Bot\CreateGameInvitation;
use App\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use App\Traits\GameHelperTrait;
use App\Traits\RegisterHelperTrait;
use Illuminate\Http\Request;

class GameSharingController extends Controller
{
    use RegisterHelperTrait, GameHelperTrait;

    public function index(Request $request): JsonApiResponse
    {
        /** @var User $user endpoint guarded by api guard */
        $user = $request->user();

        $gameId = $this->getCurrentUserGameActivity($user->id);
        $sharedGames = Redis::get('bot:game:shared') ?? [];

        if (in_array($gameId, array_keys($sharedGames), true)) {
            return new JsonApiResponse(['message' => "The game $gameId has already been shared."], Status::BAD_REQUEST);
        }

        $game = $this->getGame($gameId);

        broadcast(new CreateGameInvitation($game));

        return new JsonApiResponse(['message' => 'Game successfully shared.']);
    }
}
