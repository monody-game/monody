<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Responses\JsonApiResponse;
use App\Models\GameOutcome;
use App\Models\Statistic;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatisticsController extends Controller
{
    public function show(Request $request, string $userId = null): JsonApiResponse
    {
        if ($userId === null && $request->user() === null) {
            return new JsonApiResponse(['userId' => 'Field required.'], Status::UNPROCESSABLE_ENTITY);
        }

        $userId = $userId ?? $request->user()?->id;

        $stats = [
            'win_streak' => 0,
            'longest_streak' => 0,
            'wins' => 0,
            'losses' => 0,
            'highest_win_role' => null,
            'most_possessed_role' => null,
        ];

        $outcomes = GameOutcome::whereHas('users', fn ($query) => $query->where('user_id', $userId));

        if ($outcomes->doesntExist()) {
            return new JsonApiResponse(['statistics' => $stats]);
        }

        $outcomes = $outcomes->get();

        $userStats = $outcomes->map(
            fn ($outcome) => $outcome->load('users')
                ->users
                ->filter(fn ($user) => $user->id === $userId)
                ->first()
        );

        $stats['wins'] = $userStats->where('pivot.win', true)->count();
        $stats['losses'] = $userStats->where('pivot.win', false)->count();

        $highestPossession = $userStats->map(fn ($outcome) => $outcome['pivot']['role'])->countBy()->sortDesc();
        $highestPossessionOccurences = $highestPossession->first();

        if ($highestPossessionOccurences !== null) {
            /** @var int $role */
            $role = $highestPossession->search($highestPossessionOccurences);

            $stats['most_possessed_role'] = [
                'role' => Role::from($role),
                'occurences' => $highestPossessionOccurences,
            ];
        }

        $highestWinRole = $userStats->where('pivot.win', true)->map(fn ($outcome) => $outcome['pivot']['role'])->countBy()->sortDesc();
        $highestWinRoleOccurences = $highestWinRole->first();

        if ($highestWinRoleOccurences !== null) {
            $role = $highestWinRole->search($highestWinRoleOccurences);

            $stats['highest_win_role'] = [
                'role' => $role,
                'occurences' => $highestWinRoleOccurences,
            ];
        }

        /** @var Statistic $userStats Because stats are created during registration */
        $userStats = Statistic::where('user_id', $userId)->first();
        $stats['win_streak'] = $userStats['win_streak'];
        $stats['longest_streak'] = $userStats['longest_streak'];

        return JsonApiResponse::make(['statistics' => $stats])->withCache(Carbon::now()->addMinutes(5));
    }
}
