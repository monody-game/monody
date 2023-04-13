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
     * @return Collection<int, array{information: int, user: User}>
     */
    private function byElo(): Collection
    {
        return Elo::limit(10)
            ->orderBy('elo', 'desc')
            ->get()
            ->map(function (Elo $elo) {
                /** @var User $user */
                $user = User::select(['id', 'username', 'avatar', 'level'])->find($elo->user_id);
                unset($elo['user_id']);

                return [
                    'information' => $elo->elo,
                    'user' => $user,
                ];
            });
    }

    /**
     * @return Collection<int, array{information: int, user: User}>
     */
    private function byLevel(): Collection
    {
        return User::limit(10)
            ->select(['id', 'username', 'avatar', 'level'])
            ->orderBy('level', 'desc')
            ->get()
            ->map(fn (User $user) => ['information' => $user->level, 'user' => $user]);
    }

    /**
     * @return Collection<int, array{information: int, user: User}>
     */
    private function byWins(): Collection
    {
        return GameOutcome::limit(10)
            ->selectRaw('count(win) as wins, user_id')
            ->where('win', true)
            ->groupBy('user_id')
            ->orderBy('wins', 'desc')
            ->get()
            ->map(function (GameOutcome $outcome) {
                /** @var int $wins */
                $wins = $outcome['wins'];

                /** @var User $user */
                $user = User::select(['id', 'username', 'avatar', 'level'])->find($outcome->user_id);

                return [
                    'information' => $wins,
                    'user' => $user,
                ];
            });
    }
}
