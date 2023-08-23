<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Services\ListGamesService;

class GameListController extends Controller
{
    public function __construct(
        private readonly ListGamesService $service
    ) {
    }

    public function list(string $type = '*'): JsonApiResponse
    {
        $list = $this->service->list($type);

        return JsonApiResponse::make(['games' => $list])->withoutCache();
    }
}
