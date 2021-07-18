<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use ArrayObject;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {        
        $validateData = $request->validate([
            'username' => 'required|string|max:24',
            'password' => 'required|string|min:6',
            'remember_me' => 'required|boolean'
        ]);
        
        $attempt = new ArrayObject($validateData);
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
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|max:24',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        $validatedData['password'] = bcrypt($request->password);

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([ 'user' => $user, 'access_token' => $accessToken]);
    }

    /**
     * @return JsonResponse
     */
    public function logout (Request $request): JsonResponse
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