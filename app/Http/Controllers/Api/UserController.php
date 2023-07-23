<?php

namespace App\Http\Controllers\Api;

use App\Enums\AlertType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function user(Request $request): JsonApiResponse
    {
        return JsonApiResponse::make([
            'user' => $request->user()?->makeVisible([
                'email',
                'email_verified_at',
                'discord_linked_at',
            ]),
        ])->withCache(Carbon::now()->addHour());
    }

    public function update(UserUpdateRequest $request): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->has('email') && $request->get('email') !== $user->email) {
            $user->email_verified_at = null;
            $user->clearDiscord();
        }

        foreach ($request->validated() as $field => $value) {
            $user->{$field} = $value;
        }

        $user->save();

        $userResponse = $user->makeVisible([
            'email',
            'email_verified_at',
            'discord_linked_at',
        ])->toArray();

        $userResponse['email'] = Str::obfuscateEmail($user->email ?? $request->get('email'));

        if ($request->has('email') && $user->hasVerifiedEmail() === false) {
            $user->sendEmailVerificationNotification();

            return JsonApiResponse::make([
                'user' => $user->makeVisible([
                    'email',
                    'email_verified_at',
                    'discord_linked_at',
                ]),
            ])->withPopup(
                AlertType::Info,
                __('mail.sent', ['email' => $userResponse['email']]),
                __('mail.wait'),
                route('verification.send', [], false),
                __('mail.send_link')
            )
                ->flushCacheFor('/user');
        }

        return JsonApiResponse::make([
            'user' => $user,
        ])->flushCacheFor('/user');
    }

    public function discord(string $discordId): JsonApiResponse
    {
        $user = User::where('discord_id', $discordId)->get();

        if (!$user->first()) {
            return new JsonApiResponse(['message' => 'You need to link your discord account first'], Status::UNAUTHORIZED);
        }

        return new JsonApiResponse(['user' => $user->first()]);
    }
}
