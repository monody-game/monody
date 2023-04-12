<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\Elo;
use App\Models\GameOutcome;
use App\Models\User;
use Illuminate\Support\Collection;

class LeaderboardController extends Controller
{
    public function index(string $leaderboard): JsonApiResponse
    {
        $leaderboards = [
            'elo',
            'level',
            'wins',
        ];

        if (!in_array($leaderboard, $leaderboards, true)) {
            return new JsonApiResponse(['message' => "The leaderboard {$leaderboard} is not valid."], Status::UNPROCESSABLE_ENTITY);
        }

        return match ($leaderboard) {
            'elo' => new JsonApiResponse(['board' => $this->byElo()]),
            'level' => new JsonApiResponse(['board' => $this->byLevel()]),
            'wins' => new JsonApiResponse(['board' => $this->byWins()]),
        };
    }

    /**
     * @return Collection<int, Elo>
     */
    private function byElo(): Collection
    {
        return Elo::limit(10)
            ->orderBy('elo', 'desc')
            ->get()
            ->map(function ($elo) {
                $elo['user'] = User::select(['id', 'username', 'avatar', 'level'])->find($elo->user_id);
                unset($elo['user_id']);

                return $elo;
            });
    }

    /**
     * @return Collection<int, User>
     */
    private function byLevel(): Collection
    {
        return User::limit(10)
            ->select(['id', 'username', 'avatar', 'level'])
            ->orderBy('level', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, array{wins: int, user: User}>
     */
    private function byWins(): Collection
    {
        return GameOutcome::limit(10)
            ->selectRaw('count(win) as wins, user_id')
            ->where('win', true)
            ->groupBy('user_id')
            ->orderBy('wins', 'desc')
            ->get()
            ->map(function ($outcome) {
                /** @var int $wins */
                $wins = $outcome['wins'];

                /** @var User $user */
                $user = User::select(['id', 'username', 'avatar', 'level'])->find($outcome->user_id);

                return [
                    'wins' => $wins,
                    'user' => $user,
                ];
            });
    }
}
