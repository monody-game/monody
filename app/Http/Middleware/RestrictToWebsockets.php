<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToWebsockets
{
    public function handle(Request $request, Closure $next)
    {
		if($request->header('host') && $request->header('host') === 'web') {
			return $next($request);
		}

		return new JsonResponse('Unauthorized', Response::HTTP_FORBIDDEN);
    }
}
