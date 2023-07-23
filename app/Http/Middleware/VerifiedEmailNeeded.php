<?php

namespace App\Http\Middleware;

use App\Enums\AlertType;
use App\Enums\Status;
use App\Http\Responses\JsonApiResponse;
use Closure;
use Illuminate\Http\Request;

class VerifiedEmailNeeded
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->user() || $request->user()->hasVerifiedEmail() === false) {
            return JsonApiResponse::make(['message' => 'You must verify your email in order to perform this action.'], Status::UNAUTHORIZED)
                ->withAlert(AlertType::Error, __('errors.mail'));
        }

        return $next($request);
    }
}
