<?php

namespace App\Http\Controllers\Api;

use App\Enums\Badge;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\UserBadge as BadgeModel;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function get(Request $request, string $userId = null): JsonApiResponse
    {
        $userBadges = [];

        if ($request->user() !== null || $userId !== null) {
            $user = $request->user()->id ?? $userId;
            $userBadges = BadgeModel::select('id', 'level', 'badge_id', 'obtained_at')->where('user_id', $user)->get();
        }

        $badges = collect(Badge::cases());

        $list = [];

        foreach ($badges as $badge) {
            $list[$badge->value] = $badge->full();
        }

        foreach ($userBadges as $userBadge) {
            $list[$userBadge->badge_id]['owned'] = true;
            $list[$userBadge->badge_id]['current_level'] = $userBadge->level;
            $list[$userBadge->badge_id]['obtained_at'] = $userBadge->obtained_at;
        }

        return new JsonApiResponse([
            'badges' => [...$list],
        ]);
    }
}
