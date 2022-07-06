<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToDockerNetwork
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
