<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function verify(EmailVerificationRequest $request): JsonApiResponse
    {
        $request->fulfill();

        return JsonApiResponse::make(status: Status::TEMPORARY_REDIRECT, headers: ['Location' => '/play'])
            ->withAlert(AlertType::Success, __('mail.verified'));
    }

    public function notice(Request $request): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->sendEmailVerificationNotification();

        return JsonApiResponse::make()
            ->withPopup(
                AlertType::Info,
                __('mail.sent', ['email' => $user['email']]),
                __('mail.spam_notice')
            )
            ->withoutCache();
    }
}
