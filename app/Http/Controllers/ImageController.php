<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    public function show(Server $server, string $path): Response
    {
        try {
            /** @var StreamedResponse $res */
            $res = $server->getImageResponse($path, request()->all());
            $res->setMaxAge(5 * 60);

            return $res;
        } catch (FileNotFoundException $e) {
            return new JsonResponse([
                'message' => 'File not found.',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
