<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AlertType;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LogoutController extends Controller
{
    public function index(Request $request): JsonApiResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Collection<int, Model> $tokens User must be connected to access this route */
        $tokens = $user->tokens()->where('name', $request->getClientIp() ?? $user->id)->get();

        if ($tokens->count() < 1) {
            $tokens = [$user->tokens()->orderByDesc('created_at')->first()];
        }

        foreach ($tokens as $token) {
            /** @var Model $token */
            $token->delete();
        }

        Cookie::expire('monody_access_token');

        return JsonApiResponse::make()
            ->withAlert(AlertType::Success, __('auth.bye'))
            ->flushCacheFor('/user');
    }

    public function all(Request $request): JsonApiResponse
    {
        $request->user()?->tokens()->delete();

        Cookie::expire('monody_access_token');

        return JsonApiResponse::make()
            ->withAlert(AlertType::Success, __('auth.bye'))
            ->flushCacheFor('/user');
    }
}
