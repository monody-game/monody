<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($locale = $request->header('X-App-Locale')) {
            App::setLocale($locale);

            return $next($request)
                ->header('X-App-Locale', $locale);
        }

        if ($request->header('Accept-Language') !== null) {
            foreach (explode(';', $request->header('Accept-Language')) as $langugage) {
                if (in_array($langugage, config('app.supported_locales'), true)) {
                    App::setLocale($langugage);
                    break;
                }
            }
        }

        return $next($request);
    }
}
