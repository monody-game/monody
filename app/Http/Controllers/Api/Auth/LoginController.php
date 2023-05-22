<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonApiResponse
    {
        $credentials = $request->validated();
        $fieldType = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$fieldType => $credentials['username'], 'password' => $credentials['password']])) {
            return new JsonApiResponse(['message' => 'Invalid credentials.'], Status::BAD_REQUEST);
        }

        /** @var User $user */
        $user = Auth::user();

        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($credentials['password']);
        }

        if (Cookie::has('monody_access_token')) {
            Cookie::forget('monody_access_token');
        }

        $ip = $request->getClientIp() ?? $user->id;
        $accessToken = $user->createToken($ip)->plainTextToken;
        $cookie = Cookie::make('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', true, true, false, 'Strict');

        return JsonApiResponse::make()
            ->withAlert(AlertType::Success, 'Bon jeu !')
            ->withCookie($cookie);
    }
}
