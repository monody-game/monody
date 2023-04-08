<?php

namespace App\Services;

use App\Enums\Rank;
use App\Facades\Redis;
use App\Models\Elo;
use App\Models\User;
use App\Notifications\RankUp;
use Illuminate\Database\Eloquent\Collection;

final class EloService
{
    /**
     * Compute the elo that need to be added to user's balance.
     * Returned elo can be either a positive or negative integer, in order to use only one method (add), instead of two (add and substract)
     */
    public function computeElo(User $user, string $gameId, bool $win = true): int
    {
        $eloList = $this->getGameElo($gameId)->map(fn ($model) => $model->elo);
        $maxElo = $eloList->max();
        $minElo = $eloList->min();
        $userElo = Elo::select(['elo'])->firstOrCreate(['user_id' => $user->id]);

        if ($win === true) {
            if ($maxElo - $userElo->elo >= 500) {
                $computedElo = rand(40, 50);
            } elseif ($maxElo - $userElo->elo >= 250) {
                $computedElo = rand(20, 40);
            } else {
                $computedElo = rand(min(5, intval(($maxElo - $minElo) / 50)), min(15, intval(($maxElo - $minElo) / 40)));
            }
        } else {
            if ($maxElo - $userElo->elo >= 500) {
                $computedElo = rand(10, 25);
            } elseif ($maxElo - $userElo->elo >= 250) {
                $computedElo = rand(20, 40);
            } else {
                $computedElo = rand(min(intval(($maxElo - $minElo) / 15), 60), min(intval(($maxElo - $minElo) / 7.5), 120));
            }
        }

        if ($win) {
            return $computedElo;
        }

        return $computedElo * -1;
    }

    public function add(int $quantity, User $user): void
    {
        $elo = Elo::select('id', 'elo')->firstOrCreate(['user_id' => $user->id]);

        if (Rank::find($elo->elo) !== Rank::find($elo->elo + $quantity)) {
            $user->notify(
                new RankUp(
                    $user->id, Rank::find($elo->elo + $quantity)
                )
            );
        }

        $elo->elo += $quantity;
        $elo->save();
    }

    /**
     * Return the elo of each game player
     *
     * @return Collection<int, Elo>
     */
    private function getGameElo(string $gameId): Collection
    {
        $game = Redis::get("game:$gameId");

        return Elo::select(['elo'])
            ->whereIn('user_id', $game['users'])
            ->get();
    }
}
