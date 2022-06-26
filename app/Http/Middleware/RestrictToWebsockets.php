<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToWebsockets
{
    public function __construct(private readonly Application $app)
    {
    }

    public function handle(Request $request, Closure $next): Closure|JsonResponse
    {
        if (($request->getHost() && 'web' === $request->getHost()) || $this->app->runningUnitTests()) {
            return $next($request);
        }

        return new JsonResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }
}
