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
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $attempt = new ArrayObject($request->validated());
        unset($attempt['remember_me']);
        $attempt = $attempt->getArrayCopy();

        if (!Auth::attempt($attempt)) {
            return new JsonResponse(['message' => 'Invalid Credentials'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = Auth::user();

        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($attempt['password']);
        }

        if (Cookie::has('monody_access_token')) {
            Cookie::forget('monody_access_token');
        }

        $accessToken = $user->createToken('authToken')->accessToken;
        $cookie = Cookie::make('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', true, true, false, 'Strict');

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
