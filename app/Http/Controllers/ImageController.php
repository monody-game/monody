<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Http\Responses\JsonApiResponse;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    public function show(Server $server, string $path): JsonApiResponse|StreamedResponse
    {
        try {
            /** @var StreamedResponse $res */
            $res = $server->getImageResponse($path, request()->all());
            $res->setMaxAge(5 * 60);

            return $res;
        } catch (FileNotFoundException $e) {
            return new JsonApiResponse([
                'message' => 'File not found.',
            ], Status::NOT_FOUND);
        }
    }
}
