<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class OptionalAuthentication extends Authenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, ['api']);
        } catch (AuthenticationException $e) {
            // Don't do anything as authentication needs to be optional
        }

        return $next($request);
    }

    protected function unauthenticated($request, array $guards)
    {
        // Don't do anything, same as above
    }
}
