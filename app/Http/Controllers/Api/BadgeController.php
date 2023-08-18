<?php

namespace App\Http\Controllers\Api;

use App\Enums\Badge;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\UserBadge as BadgeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BadgeController extends Controller
{
    public function get(Request $request, string $userId = null): JsonApiResponse
    {
        $userBadges = [];

        if ($request->user() !== null || $userId !== null) {
            $user = $request->user()->id ?? $userId;
            $userBadges = BadgeModel::select('id', 'level', 'badge_id', 'obtained_at')->where('user_id', $user)->get();
        }

        $list = [];

        foreach (Badge::cases() as $badge) {
            $list[$badge->value] = $badge->full();
        }

        foreach ($userBadges as $userBadge) {
            $list[$userBadge->badge_id->value]['owned'] = true;
            $list[$userBadge->badge_id->value]['current_level'] = $userBadge->level;
            $list[$userBadge->badge_id->value]['obtained_at'] = $userBadge->obtained_at;
        }

        return JsonApiResponse::make([
            'badges' => [...$list],
        ])->withCache(Carbon::now()->addMinutes(10));
    }
}
