<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:60',
            'password' => 'required|string|min:4|confirmed',
        ]);

        if ($validator->fails()) {
            return response(['errors'=>$validator->errors()->all()], 422);
        }

        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response, 200);
    }

    public function login(Request $request)
    {
        $content = json_decode($request->getContent(), true);
        $validator = Validator::make($content, [
            'username' => 'required|string|max:60',
            'password' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return response(['errors'=> $validator->errors()->all()], 422);
        }

        $user = User::where('username', $content['username'])->first();

        if ($user) {
            if (Hash::check($content['password'], $user->password)) {
                return response(['token' => 'test'], 200);
            } else {
                return response(['message'=> 'password mismatch'], 422);
            }
        } else {
            return response(['message' => 'User does not exist'], 422);
        }
    }

    public function logout (Request $request)
    {
        $request->user()->token()->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
}
