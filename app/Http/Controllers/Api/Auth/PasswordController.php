<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    public function reset(Request $request): JsonApiResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? JsonApiResponse::make()
                ->withPopup(
                    AlertType::Success,
                    __('passwords.sent'),
                    __('passwords.wait')
                )
                ->withoutCache()
            : JsonApiResponse::make(status: Status::BAD_REQUEST)
                ->withAlert(AlertType::Error, __('errors.error', ['error' => __($status)]))
                ->withoutCache();
    }

    public function token(Request $request): JsonApiResponse
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
            ? new JsonApiResponse()
            : JsonApiResponse::make(status: Status::BAD_REQUEST)
                ->withAlert(AlertType::Error, __('errors.error', ['error' => __($status)]))
                ->withoutCache();
    }
}
