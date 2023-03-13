<?php

namespace App\Http\Middleware;

use App\Enums\AlertType;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedEmailNeeded
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->user() || $request->user()->hasVerifiedEmail() === false) {
            return (new JsonResponse([], Response::HTTP_UNAUTHORIZED))
                ->withMessage('You must verify your email in order to perform this action.')
                ->withAlert(AlertType::Error, 'Vous devez lier une email à votre compte et la vérifier.');
        }

        return $next($request);
    }
}
