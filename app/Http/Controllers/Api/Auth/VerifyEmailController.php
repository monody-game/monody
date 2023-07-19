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
            ->withAlert(AlertType::Success, 'Email vérifié avec succès !');
    }

    public function notice(Request $request): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->sendEmailVerificationNotification();

        return JsonApiResponse::make()
            ->withPopup(
                AlertType::Info,
                "Un mail de vérification vient de vous être envoyé à l'adresse {$user['email']}. Veuillez vérifier votre email en cliquant sur le lien",
                'Pensez à vérifier vos spams !'
            )
            ->withoutCache();
    }
}
