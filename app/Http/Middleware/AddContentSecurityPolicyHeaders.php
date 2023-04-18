<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AddContentSecurityPolicyHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        if ($response instanceof StreamedResponse) {
            return $response;
        }

        $csp = "script-src 'nonce-" . Vite::cspNonce() . "' 'unsafe-inline'";

        if (!app()->isProduction()) {
            $csp .= " 'unsafe-eval'";
        }

        return $response->withHeaders([
            'Content-Security-Policy' => $csp . "; object-src 'none'; base-uri 'self'; form-action 'self'",
        ]);
    }
}
