<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use ArrayObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $attempt = new ArrayObject($request->all());
        unset($attempt['remember_me']);
        if (!Auth::attempt($attempt->getArrayCopy())) {
            return response()->json(['message' => 'Invalid Credentials']);
        }

        /**
         * @var User $user
         */
        $user = Auth::user();

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json(['access_token' => $accessToken]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->all();

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json(['access_token' => $accessToken]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->token()?->revoke();

        return response()->json([
            'message' => 'You have been successfully logged out!'
        ]);
    }

    public function user(): JsonResponse
    {
        return response()->json(request()->user());
    }
}
