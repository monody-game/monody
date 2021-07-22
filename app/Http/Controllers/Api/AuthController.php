<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use ArrayObject;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $attempt = new ArrayObject($request->all());
        unset($attempt['remember_me']);

        if (!Auth::attempt($attempt->getArrayCopy())) {
            return response(['message' => 'Invalid Credentials']);
        }

        $accessToken = Auth::user()->createToken('authToken')->accessToken;

        return response()->json(['user' => auth()->user(), 'access_token' => $accessToken]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->all();

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json(['user' => $user, 'access_token' => $accessToken]);
    }

    /**
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'You have been successfully logged out!'
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function user(): JsonResponse
    {
        return response()->json(request()->user());
    }
}
