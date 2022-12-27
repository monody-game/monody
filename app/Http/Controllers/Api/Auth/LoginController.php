<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
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
        $credentials = $request->validated();
        if (!Auth::attempt($credentials)) {
            return (new JsonResponse(null, Response::HTTP_UNAUTHORIZED))
                ->withMessage('Invalid credentials.');
        }

        /** @var User $user */
        $user = Auth::user();

        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($credentials['password']);
        }

        if (Cookie::has('monody_access_token')) {
            Cookie::forget('monody_access_token');
        }

        $accessToken = $user->createToken('authToken')->accessToken;
        $cookie = Cookie::make('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', true, true, false, 'Lax');

        return (new JsonResponse([]))
            ->withAlert(AlertType::Success, 'Bon jeu !')
            ->cookie($cookie);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->token()?->revoke();

        Cookie::queue(Cookie::forget('monody_access_token'));

        return (new JsonResponse([]))
            ->withAlert(AlertType::Success, 'À bientôt !');
    }
}
