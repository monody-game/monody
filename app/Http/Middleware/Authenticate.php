<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        abort(response(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED));
    }
}
