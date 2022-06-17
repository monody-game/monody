<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        return new JsonResponse($request->user());
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        foreach ($request->validated() as $field => $value) {
            $user->{$field} = $value;
        }

        $user->save();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
