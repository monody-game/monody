<?php

namespace App\Http\Controllers;

use League\Glide\Server;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    public function show(Server $server, string $path): StreamedResponse
    {
        /** @var StreamedResponse $res */
        $res = $server->getImageResponse($path, request()->all());
        $res->setMaxAge(5 * 60);

        return $res;
    }
}
