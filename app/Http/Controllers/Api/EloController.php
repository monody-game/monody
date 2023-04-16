<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\Elo;
use App\Models\User;
use Illuminate\Http\Request;

class EloController extends Controller
{
    public function show(Request $request, ?string $userId = null): JsonApiResponse
    {
        if ($userId === null && $request->user() === null) {
            return new JsonApiResponse(['userId' => 'Field required.'], Status::UNPROCESSABLE_ENTITY);
        }

        $userId = $userId ?? $request->user()?->id;

        if (!User::where('id', $userId)->exists()) {
            return new JsonApiResponse(['message' => "Unable to find user with id \"{$userId}\""], Status::NOT_FOUND);
        }

        $elo = Elo::firstOrCreate([
            'user_id' => $userId,
        ]);

        return new JsonApiResponse([
            'elo' => $elo,
        ]);
    }
}
