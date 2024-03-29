<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;

class PingController extends Controller
{
    public function ping(): JsonApiResponse
    {
        return JsonApiResponse::make(data: [
            'message' => 'Alive 🌙',
        ])->withoutCache();
    }
}
