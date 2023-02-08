<?php

namespace App\Http\Controllers;

use League\Glide\Server;

class ImageController extends Controller
{
    public function show(Server $server, string $path): mixed
    {
        return $server->getImageResponse($path, request()->all());
    }
}
