<?php

namespace App\Http\Controllers\Api;

use App\Enums\AlertType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        return new JsonResponse($request->user());
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->has('email') && $request->get('email') !== $user->email) {
            $user->email_verified_at = null;
        }

        foreach ($request->validated() as $field => $value) {
            $user->{$field} = $value;
        }

        $user->save();

        if ($request->has('email') && $user->hasVerifiedEmail() === false) {
            $user->sendEmailVerificationNotification();

            return (new JsonResponse($user))
                ->withPopup(
                    AlertType::Info,
                    "Un mail de vérification vient de vous être envoyé à l'adresse {$user['email']}. Veuillez vérifier votre email en cliquant sur le lien",
                    "Il peut s'écouler quelques minutes avant de recevoir le mail. Si vous ne le recevez pas, cliquez ",
                    route('verification.send', [], false),
                    'ici pour renvoyer le lien.'
                );
        }

        return new JsonResponse($user);
    }
}
