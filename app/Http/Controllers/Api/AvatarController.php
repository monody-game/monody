<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvatarUploadRequest;
use App\Models\User;
use App\Services\AvatarGenerator;
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

        $path = $this->generator->toStoragePath($user->avatar);

        Storage::delete("avatars/$path");

        Storage::put("avatars/$path", $result);

        $user->avatar = str_replace('storage', 'assets', Storage::url("$path"));
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

        $user->avatar = str_replace('storage', 'assets', Storage::url("avatars/$user->id.png"));
        $user->save();

        return (new JsonResponse(null, Response::HTTP_CREATED))
            ->withMessage('Avatar successfully uploaded');
    }

    public function delete(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $path = str_replace('/assets/', '', $user->avatar);

        Storage::delete($path);
        $user->avatar = str_replace('storage', 'assets', Storage::url('avatars/default.png'));
        $user->save();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
