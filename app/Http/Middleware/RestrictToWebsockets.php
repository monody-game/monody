<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToWebsockets
{
    /**
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->getHost() && 'web' === $request->getHost()) {
            return $next($request);
        }

        return new JsonResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }
}
