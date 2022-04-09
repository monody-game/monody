<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddTokenToRequests
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken()) {
            if ($request->hasCookie('monody_access_token')) {
                $token = $request->cookie('monody_access_token');

                $request->headers->add([
                    'Authorization' => 'Bearer ' . $token
                ]);
            }
        }

        return $next($request);
    }
}
