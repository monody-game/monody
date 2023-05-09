<?php

namespace App\Http\Middleware;

use App\Enums\Status;
use App\Http\Responses\JsonApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestrictRequest
{
    public function handle(Request $request, Closure $next): JsonApiResponse|JsonResponse
    {
        if ($request->input('token') === 'weshcesttropsecretmaisazy') {
            return $next($request);
        }

        return new JsonApiResponse(['message' => 'Unauthorized.'], Status::UNAUTHORIZED);
    }
}
