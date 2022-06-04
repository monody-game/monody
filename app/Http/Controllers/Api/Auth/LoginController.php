<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use ArrayObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $attempt = new ArrayObject($request->validated());
        unset($attempt['remember_me']);

        if (!Auth::attempt($attempt->getArrayCopy())) {
            return new JsonResponse(['message' => 'Invalid Credentials'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = Auth::user();

        $accessToken = $user->createToken('authToken')->accessToken;
        $cookie = cookie('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', false, true, false, 'Strict');

        return (new JsonResponse([], Response::HTTP_NO_CONTENT))->cookie($cookie);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->token()?->revoke();

        Cookie::queue(Cookie::forget('monody_access_token'));

        return new JsonResponse([
            'message' => 'You have been successfully logged out!'
        ]);
    }
}
