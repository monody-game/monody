<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonApiResponse
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

        $response = JsonApiResponse::make(status: Status::CREATED)
            ->withAlert(AlertType::Success, 'Votre compte a bien été créé');

        if ($request->has('email')) {
            $response = $response
                ->withPopup(
                    AlertType::Info,
                    "Un mail de vérification vient de vous être envoyé à l'adresse {$user['email']}. Veuillez vérifier votre email en cliquant sur le lien",
                    "Il peut s'écouler quelques minutes avant de recevoir le mail. Si vous ne le recevez pas, cliquez ",
                    route('verification.send', [], false),
                    'ici pour renvoyer le lien.'
                );
        }

        return $response->withCookie($cookie);
    }
}
