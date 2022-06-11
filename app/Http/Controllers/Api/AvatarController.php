<?php

namespace App\Http\Controllers\Api;

use App\AvatarGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AvatarUploadRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AvatarController extends Controller
{
    private AvatarGenerator $generator;

    public function __construct()
    {
        $this->generator = new AvatarGenerator();
    }

    public function generate(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->generator->generate($user);

        if (!$result) {
            return new JsonResponse(['message' => 'Error while generating the avatar'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $path = $this->generator->toStoragePath($user->avatar);

        Storage::delete("avatars/$path");

        Storage::put("avatars/$path", $result);

        $user->avatar = Storage::url("$path");
        $user->save();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function upload(AvatarUploadRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validated('avatar')->storeAs(
            'avatars', "{$user->id}.png"
        );

        $user->avatar = Storage::url((string) $user->id);
        $user->save();

        return new JsonResponse(['message' => 'Avatar uploaded !'], 201);
    }
}
