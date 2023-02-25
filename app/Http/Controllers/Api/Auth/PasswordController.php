<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class PasswordController extends Controller
{
    public function reset(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? (new JsonResponse())->withPopup(
                AlertType::Success,
                'Un email vient de vous être envoyé avec un lien pour changer votre mot de passe !',
                'le mail peut mettre quelques minutes à arriver, veillez à regarder dans vos spams également. Vous pouvez fermer cette page'
            )
            : (new JsonResponse(null, Response::HTTP_BAD_REQUEST))->withAlert(AlertType::Error, 'Une erreur est survenue : ' . __($status));
    }

    public function token(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ]);

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? new JsonResponse()
            : (new JsonResponse(null, Response::HTTP_BAD_REQUEST))->withAlert(AlertType::Error, 'Une erreur est survenue : ' . __($status));
    }
}