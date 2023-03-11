<?php

namespace App\Http\Controllers\Api;

use App\Enums\Badges;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function get(Request $request, string $userId = null): JsonResponse
    {
        $userBadges = [];

        if ($request->user() !== null || $userId !== null) {
            $user = $request->user()->id ?? $userId;
            $userBadges = Badge::select('id', 'level', 'badge_id', 'obtained_at')->where('user_id', $user)->get();
        }

        $badges = collect(Badges::cases());
        $badges = $badges->filter(fn ($badge) => $badge !== Badges::Owner);

        $list = [];

        foreach ($badges as $badge) {
            $list[$badge->value] = [
                'id' => $badge->value,
                'name' => $badge->name(),
                'display_name' => $badge->stringify(),
                'explanation' => $badge->describe(),
                'description' => $badge->description(),
                'owned' => false,
                'max_level' => $badge->maxLevel(),
                'current_level' => 0,
                'obtained_at' => null,
            ];
        }

        foreach ($userBadges as $userBadge) {
            $list[$userBadge->badge_id]['owned'] = true;
            $list[$userBadge->badge_id]['current_level'] = $userBadge->level;
            $list[$userBadge->badge_id]['obtained_at'] = $userBadge->obtained_at;
        }

        return new JsonResponse([...$list]);
    }
}
