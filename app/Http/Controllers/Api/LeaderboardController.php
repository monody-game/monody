<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\Elo;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        // Magic query from stackoverflow... Does exactly what we need, if it fails, I can't fix it 👍
        $results = collect(DB::select("
			SELECT user_id, COUNT(*) as wins
			FROM game_outcomes
		    CROSS JOIN JSON_TABLE(game_outcomes.winning_users, '$[*]' COLUMNS (user_id VARCHAR(255) PATH '$')) jsontable
		    GROUP BY user_id
		    ORDER BY wins DESC
		"));

        /** @phpstan-ignore-next-line too hard to make phpstan understand laravel's magic ... */
        return $results
            ->map(function ($result) {
                $user = User::find($result->user_id);

                // We still check the case where the user is deleted
                if ($user === null) {
                    return false;
                }

                return ['user' => $user, 'information' => $result->wins];
            })
            ->filter(); // We delete rows were users does not exist
    }
}
