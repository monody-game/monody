<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PingController extends Controller
{
    public function ping(): JsonResponse
    {
        return (new JsonResponse())
            ->withMessage('Alive 🌙');
    }
}
