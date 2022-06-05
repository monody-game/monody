<?php

namespace App\Http\Controllers\Api;

use App\AvatarGenerator;
use App\Http\Controllers\Controller;
use App\Models\User;
use const DIRECTORY_SEPARATOR;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AvatarController extends Controller
{
    private AvatarGenerator $generator;
    private string $basePath;

    public function __construct()
    {
        $this->basePath = \dirname(__DIR__, 4) .
            DIRECTORY_SEPARATOR . 'public' .
            DIRECTORY_SEPARATOR . 'images' .
            DIRECTORY_SEPARATOR . 'avatars';
        $this->generator = new AvatarGenerator($this->basePath);
    }

    public function generate(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $result = $this->generator->generate($user);
        if (true === $result) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(['message' => 'Error while generating the avatar'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
