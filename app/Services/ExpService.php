<?php

namespace App\Services;

use App\Enums\Badges;
use App\Models\Exp;
use App\Models\User;
use App\Notifications\ExpEarned;
use App\Notifications\LevelUp;

class ExpService
{
    public function __construct(
        private readonly BadgeService $badgeService,
    ) {
    }

    /**
     * @var float Exponent used for the exp formula
     */
    public const DIFFICULTY = 1.7;

    /**
     * Add exp to user's balance
     */
    public function add(int $quantity, User $user): void
    {
        $exp = Exp::firstOrCreate(['user_id' => $user->id]);
        $nextLevel = $this->nextLevelExp($user->level);
        $hasLeveledUp = false;

        $exp->exp += $quantity;

        while ($exp->exp >= $nextLevel) {
            $user->level++;
            $user->save();

            $difference = $exp->exp - $nextLevel;
            $exp->exp = $difference;

            $hasLeveledUp = true;
            $nextLevel = $this->nextLevelExp($user->level);
        }

        if ($hasLeveledUp) {
            $user->notify(new LevelUp([
                'user_id' => $user->id,
                'level' => $user->level,
                'exp_needed' => $this->nextLevelExp($user->level),
            ]));

            if ($this->badgeService->canAccess($user, Badges::Level)) {
                $this->badgeService->add($user, Badges::Level);
            }
        }

        $exp->save();

        $user->notify(new ExpEarned($exp));
    }

    /**
     * Return the exp needed to get to the level passed in parameter
     *
     * @example nextLevelExp(2) // Returns the exp needed to pass from level 2 to level 3
     */
    public function nextLevelExp(int $level): int
    {
        // Using intval because floor return a float
        return intval(floor(10 * (pow($level, self::DIFFICULTY))));
    }
}
