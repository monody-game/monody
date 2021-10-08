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
        $this->generator = new AvatarGenerator();
    }

    /**
     * @param Filesystem $filesystem
     * @param Request $request
     * @param string $path
     * @return mixed
     */
    public function show(Filesystem $filesystem, Request $request, string $path)
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
