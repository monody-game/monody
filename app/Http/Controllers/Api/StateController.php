<?php

namespace App\Http\Controllers\Api;

use App\Enums\States;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    public function get(int $state): JsonResponse
    {
        $stateDetails = States::from($state);

        return new JsonResponse([
            'state' => $state,
            'icon' => $stateDetails->iconify(),
            'raw_name' => $stateDetails->stringify(),
            'name' => $stateDetails->readeableStringify(),
            'duration' => $stateDetails->duration(),
            'background' => $stateDetails->background(),
        ]);
    }

    public function message(int $state): JsonResponse
    {
        $message = States::from($state)->message();

        if ($message === null) {
            return new JsonResponse(['No message registered for this state'], 404);
        }

        return new JsonResponse(['message' => States::from($state)->message()]);
    }
}
