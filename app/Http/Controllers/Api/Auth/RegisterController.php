<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['password'] = Hash::make($request->password);

        $user = User::create($data);

        $accessToken = $user->createToken('authToken')->plainTextToken;
        $cookie = Cookie::make('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', true, true, false, 'Strict');

        $user->sendEmailVerificationNotification();

        $stats = new Statistic();
        $stats->user_id = $user->id;
        $stats->save();

        return (new JsonResponse(null, Response::HTTP_CREATED))
                ->withAlert(AlertType::Success, 'Votre compte a bien été créé')
                ->withPopup(
                    AlertType::Info,
                    "Un mail de vérification vient de vous être envoyé à l'adresse {$user['email']}. Veuillez vérifier votre email en cliquant sur le lien",
                    "Il peut s'écouler quelques minutes avant de recevoir le mail. Si vous ne le recevez pas, cliquez sur ",
                    route('verification.send'),
                    'ici pour renvoyer le lien.'
                )
                ->withCookie($cookie);
    }
}
