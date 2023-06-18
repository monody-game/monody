<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddTokenToRequests
{
    /**
     * Attach cookie access token to the Authorization header in the request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->bearerToken()) {
            if ($request->hasCookie('monody_access_token')) {
                /** @var string $token */
                $token = $request->cookie('monody_access_token');

                $request->headers->add([
                    'Authorization' => 'Bearer ' . $token,
                ]);
            }
        }

        return $next($request);
    }
}
