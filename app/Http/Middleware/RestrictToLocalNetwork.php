<?php

namespace App\Http\Middleware;

use App\Enums\Status;
use App\Http\Responses\JsonApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestrictToLocalNetwork
{
    public function handle(Request $request, Closure $next): JsonResponse|JsonApiResponse|bool
    {
        if (
            $request->hasHeader('X-Network-Key') &&
            $request->header('X-Network-Key') === config('app.network_key')
        ) {
            return $next($request);
        }

        return new JsonApiResponse(['message' => 'Unauthorized.'], Status::FORBIDDEN);
    }
}
