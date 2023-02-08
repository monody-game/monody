<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\Filesystem;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;

class ImageController extends Controller
{
    public function show(Server $server, string $path): mixed
    {
        return $server->getImageResponse($path, request()->all());
    }
}
