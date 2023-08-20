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
        $index = rand(1, 10);

        $data['password'] = Hash::make($request->password);
        $data['avatar'] = "/assets/avatars/default_$index.png";

        $user = User::create($data);

        $ip = $request->getClientIp() ?? $user->id;
        $accessToken = $user->createToken($ip)->plainTextToken;
        $cookie = Cookie::make('monody_access_token', $accessToken, 60 * 24 * 30, '/', '', true, true, false, 'Lax');

        $user->sendEmailVerificationNotification();

        $stats = new Statistic();
        $stats->user_id = $user->id;
        $stats->save();

        $response = JsonApiResponse::make(status: Status::CREATED)
            ->withAlert(AlertType::Success, __('auth.created'));

        if ($request->has('email')) {
            $response = $response
                ->withPopup(
                    AlertType::Info,
                    __('mail.sent', ['email' => $user['email']]),
                    __('mail.wait'),
                    route('verification.send', [], false),
                    __('mail.send_link')
                );
        }

        return $response->withCookie($cookie);
    }
}
