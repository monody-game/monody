<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\AvatarUploadRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Glide\Server;

class AvatarController extends Controller
{
    public function upload(AvatarUploadRequest $request, Server $server): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();
        $file = $request->validated('avatar');

        $avatarName = explode('/', $user->avatar);
        $avatarName = $avatarName[array_key_last($avatarName)];

        if (!preg_match_all("/^default_[0-9]+\.png$/", $avatarName)) {
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
        $index = rand(1, 10);

        if (!preg_match_all("/^avatars\/default_[0-9]+\.png$/", $path)) {
            Storage::delete($path);
        }

        $user->avatar = str_replace('storage', 'assets', Storage::url("avatars/default_$index.png"));
        $user->save();

        return new JsonApiResponse(status: Status::NO_CONTENT);
    }
}
