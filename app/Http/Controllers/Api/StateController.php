<?php

namespace App\Http\Controllers\Api;

use App\Enums\State;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;

class StateController extends Controller
{
    public function get(int $state): JsonApiResponse
    {
        $stateDetails = State::from($state);

        return new JsonApiResponse([
            'state' => [
                'id' => $state,
                'icon' => $stateDetails->iconify(),
                'raw_name' => $stateDetails->stringify(),
                'name' => $stateDetails->readeableStringify(),
                'duration' => $stateDetails->duration(),
                'background' => $stateDetails->background(),
            ],
        ]);
    }

    public function message(int $state): JsonApiResponse
    {
        $message = State::from($state)->message();

        if ($message === null) {
            return new JsonApiResponse([
                'message' => 'No message registered for this state',
            ], Status::NOT_FOUND);
        }

        return new JsonApiResponse([
            'state_message' => $message,
        ]);
    }
}
