<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
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

        $accessToken = $user->createToken('authToken')->accessToken;
        $cookie = Cookie::make('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', true, true, false, 'Strict');

        $user->sendEmailVerificationNotification();
        $url = route('verification.send');

        return (new JsonResponse([
            'alerts' => [
                'success' => 'Votre compte a bien été créé',
            ],
            'popups' => [
                'info' => [
                    'content' => "Un mail de vérification vient de vous être envoyé à l'adresse {$user['email']}. Veuillez vérifier votre email en cliquant sur le lien",
                    'note' => 'Cela peut prendre quelques minutes pour recevoir le mail, si vous ne le recevez toujours pas, regardez vos spams. Sinon,',
                    'link' => $url,
                    'link_text' => 'renvoyer le lien.',
                ],
            ],
        ], Response::HTTP_CREATED))->cookie($cookie);
    }
}
