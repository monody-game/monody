<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use ArrayObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function login(LoginRequest $request): Response|JsonResponse
    {
        $attempt = new ArrayObject($request->validated());
        unset($attempt['remember_me']);

        if (!Auth::attempt($attempt->getArrayCopy())) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        $accessToken = $user->createToken('authToken')->accessToken;
        $cookie = cookie('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', false, true, false, 'Strict');

        return response('', 204)->cookie($cookie);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        $accessToken = $user->createToken('authToken')->accessToken;
        $cookie = cookie('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', false, true, false, 'Strict');

        return response()->json([], 201)->cookie($cookie);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->token()?->revoke();

        Cookie::queue(Cookie::forget('monody_access_token'));

        return response()->json([
            'message' => 'You have been successfully logged out!'
        ]);
    }

    public function user(): JsonResponse
    {
        return response()->json(request()->user());
    }
}
