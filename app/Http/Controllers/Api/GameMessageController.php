<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSended;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class GameMessageController extends Controller
{
    public function send(SendMessageRequest $request): JsonResponse
    {
        $data = $request->all();
        $game = Redis::get("game:{$data['gameId']}");

        if (!$game) {
            return response()->json("Game {$data['gameId']} not found", 404);
        }

        $game = json_decode($game, true);

        if (!\in_array($request->user()?->id, $game['users'], true)) {
            return response()->json('You must be in the game to send messages', 401);
        }

        $message = new Message($data);
        $message->set('author', $request->user());

        MessageSended::dispatch($message);

        return response()->json();
    }
}
