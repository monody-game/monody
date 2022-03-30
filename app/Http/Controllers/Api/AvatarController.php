<?php

namespace App\Http\Controllers\Api;

use App\AvatarGenerator;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;

class AvatarController extends Controller
{
    private AvatarGenerator $generator;

    public function __construct()
    {
        $this->generator = new AvatarGenerator(\dirname(__DIR__) .
            \DIRECTORY_SEPARATOR . 'public' .
            \DIRECTORY_SEPARATOR . 'images' .
            \DIRECTORY_SEPARATOR . 'avatars');
    }

    public function show(Filesystem $filesystem, Request $request, string $path): mixed
    {
        $server = ServerFactory::create([
            'response' => new LaravelResponseFactory($request),
            'cache' => $filesystem->getDriver(),
            'source' => $filesystem->getDriver(),
            'cache_path_prefix' => '.cache',
            'base_url' => 'api/avatars'
        ]);

        return $server->getImageResponse('/public/images/avatars/' . $path, $request->all());
    }

    public function generate(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $result = $this->generator->generate($user);
        if (true === $result) {
            return response()->json(['message' => 'Avatar generated successfully']);
        }

        return response()->json(['message' => 'Error while generating the avatar'], 401);
    }
}
