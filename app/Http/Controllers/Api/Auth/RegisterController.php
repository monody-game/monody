<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['password'] = Hash::make($request->password);

        $user = User::create($data);

        $accessToken = $user->createToken('authToken')->accessToken;
        $cookie = Cookie::make('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', true, true, false, 'Strict');

        $user->sendEmailVerificationNotification();

        return (new JsonResponse([], Response::HTTP_CREATED))->cookie($cookie);
    }
}
