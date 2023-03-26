<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function verify(EmailVerificationRequest $request): JsonApiResponse
    {
        $request->fulfill();

        return JsonApiResponse::make(status: Status::TEMPORARY_REDIRECT, headers: ['Location' => '/play'])
            ->withAlert(AlertType::Success, 'Email vérifié avec succès !');
    }

    public function notice(Request $request): RedirectResponse
    {
        $request->user()?->sendEmailVerificationNotification();

        return redirect('/play');
    }
}
