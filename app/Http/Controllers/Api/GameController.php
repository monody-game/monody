<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $key = env('JWT_TOKEN');
        $user = $request->user();
        $token = [
            'user_id'     => $user->getId(),
            'user_name'   => $user->getUsername(),
            'user_avatar' => $user->getAvatar(),
            'exp'         => time() + 30
        ];
        dd($token);
        $token = JWT::encode($token, $key);
        return new JsonResponse(['token' => $token]);
    }
}