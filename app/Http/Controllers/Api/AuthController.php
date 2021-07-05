<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use ArrayObject;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember_me' => 'boolean'
        ]);

        $attempt = new ArrayObject($credentials);
        unset($attempt['remember_me']);
        
        if(!Auth::attempt(["username" => "moon250", "password" => "***REMOVED***"])) {
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->all()['remember_me']) {
            $token->expires_at = Carbon::now()->addWeeks(1);
            $token->save();
            return response()->json([
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ]);
        }
        dd('not remember');
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        /** @var string $content */
        $content = $request->getContent();
        $data = json_decode($content, true);
        Validator::make($data, [
            'username' => 'required|string|unique:users',
            'password' => 'required|string|confirmed'
        ]);
        $user = new User([
            'username' => $data['username'],
            'password' => bcrypt($data['password'])
        ]);
        $user->save();
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
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
        return response()->json(request()->user(), 200);
    }
}
