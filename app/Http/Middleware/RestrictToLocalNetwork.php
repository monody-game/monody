<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToLocalNetwork
{
    public function handle(Request $request, Closure $next): JsonResponse|bool
    {
        if (
            $request->hasHeader('X-Network-Key') &&
            $request->header('X-Network-Key') === config('app.network_key')
        ) {
            return $next($request);
        }

        return new JsonResponse('Unauthorized.', Response::HTTP_FORBIDDEN);
    }
}
