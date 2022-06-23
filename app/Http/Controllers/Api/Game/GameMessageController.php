<?php

namespace App\Http\Controllers\Api\Game;

use App\Events\MessageSended;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GameMessageController extends Controller
{
    public function send(SendMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        $message = new Message($data);
        $message->set('author', [
            'id' => $request->user()?->id,
            'username' => $request->user()?->username,
            'avatar' => $request->user()?->avatar,
        ]);

        MessageSended::dispatch($message);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
