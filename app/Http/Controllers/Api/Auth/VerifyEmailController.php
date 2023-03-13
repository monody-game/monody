<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailController extends Controller
{
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return (new JsonResponse([], Response::HTTP_TEMPORARY_REDIRECT, [
            'Location' => '/play',
        ]))->withAlert(AlertType::Success, 'Email vérifié avec succès !');
    }

    public function notice(Request $request): RedirectResponse
    {
        $request->user()?->sendEmailVerificationNotification();

        return redirect('/play');
    }
}
