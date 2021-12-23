<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSended;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameMessageController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $message = new Message($request->all());

        MessageSended::dispatch($message);

        return response()->json();
    }
}
