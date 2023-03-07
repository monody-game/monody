<?php

namespace App\Services;

use App\Enums\Badges;
use App\Models\Badge;
use App\Models\User;
use App\Notifications\BadgeGranted;

class BadgeService
{
    /**
     * Retrieve user's badges
     *
     * For example, user with Beta and Wins (level 3) badges, this function will return :
     *
     * [
     * 		0 => [
     * 			"badge" => Badges::Beta,
     * 			"user_id" => "...",
     * 			"level" => 0,
     * 			"obtained_at" => "1970-01-01 00:00:00",
     * 		],
     * 		1 => [
     * 			"badge" => Badges::Wins,
     * 			"user_id" => "...",
     * 			"level" => 3,
     * 			"obtained_at" => "1970-01-01 00:00:00",
     * 		],
     * ]
     *
     * @return array<int, array{badge: Badges, user_id: string, level: int, obtained_at: string}>
     */
    public function get(User $user): array
    {
        $badges = Badge::where('user_id', $user->id)->get();

        $badges = $badges->map(function (Badge $badge) {
            return [
                'badge' => Badges::from($badge->badge_id),
                'user_id' => $badge->user_id,
                'level' => $badge->level,
                'obtained_at' => $badge->obtained_at,
            ];
        });

        return $badges->toArray();
    }

    /**
     * Grant a badge to an user with automatic level determination
     */
    public function add(User $user, Badges $badge, int $level = 1): void
    {
        $hasBadge = Badge::getUserBadge($user, $badge);

        if ($hasBadge->count() > 0 && $badge->maxLevel() <= $hasBadge->count() + 1) {
            $level = $hasBadge->count() + 1;
        }

        if ($badge->maxLevel() === -1) {
            $level = -1;
        }

        $model = new Badge();
        $model->user_id = $user->id;
        $model->badge_id = $badge->value;
        $model->level = $level;
        $model->save();

        $user->notify(new BadgeGranted([
            'badge' => $badge,
            'level' => $level,
        ]));
    }

    public function canAccess(User $user, Badges $badge): bool
    {
        $level = 1;
        $record = Badge::getUserBadge($user, $badge)->first();

        if ($record) {
            $level = $record->level + 1;
        }

        return $badge->requirementMet($user, $level);
    }
}
