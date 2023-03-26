<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\AvatarUploadRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use App\Services\AvatarGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Glide\Server;

/**
 * @deprecated Useless
 */
class AvatarController extends Controller
{
    private AvatarGenerator $generator;

    public function __construct()
    {
        $this->generator = new AvatarGenerator();
    }

    public function generate(Request $request): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->generator->generate($user);

        $path = $this->generator->toStoragePath($user->avatar);

        if ($path !== 'default.png') {
            Storage::delete("avatars/$path");
        }

        Storage::put("avatars/{$user->id}.png", $result);

        $user->avatar = str_replace('storage', 'assets', Storage::url("avatars/{$user->id}.png"));
        $user->save();

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }

    public function upload(AvatarUploadRequest $request, Server $server): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();
        $file = $request->validated('avatar');

        $avatarName = explode('/', $user->avatar);
        $avatarName = $avatarName[array_key_last($avatarName)];

        if ($avatarName !== 'default.png') {
            Storage::delete("avatars/$avatarName");
        }

        $server->deleteCache("avatars/$avatarName");

        $file->storeAs(
            'avatars', "{$user->id}.{$file->extension()}"
        );

        $user->avatar = str_replace('storage', 'assets', Storage::url("avatars/$user->id.{$file->extension()}"));
        $user->save();

        return new JsonApiResponse(['message' => 'Avatar successfully uploaded'], Status::CREATED);
    }

    public function delete(Request $request): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();
        $path = str_replace('/assets/', '', $user->avatar);

        Storage::delete($path);
        $user->avatar = str_replace('storage', 'assets', Storage::url('avatars/default.png'));
        $user->save();

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }
}
