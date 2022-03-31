<?php

namespace App\Http\Controllers\Api;

use App\AvatarGenerator;
use App\Http\Controllers\Controller;
use App\Models\User;
use const DIRECTORY_SEPARATOR;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $user = auth()->user();
        $result = $this->generator->generate($user);
        if (true === $result) {
            return response()->json(['message' => 'Avatar generated successfully']);
        }

        return response()->json(['message' => 'Error while generating the avatar'], 500);
    }
}
