<?php

namespace App\Http\Controllers\Api;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\GameOutcome;
use App\Models\Statistic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StatisticsController extends Controller
{
    public function index(Request $request, ?string $userId = null): JsonResponse
    {
        if ($userId === null && $request->user() === null) {
            return new JsonResponse('You must specify a user id.', Response::HTTP_BAD_REQUEST);
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

        if (GameOutcome::where('user_id', $userId)->doesntExist()) {
            return new JsonResponse($stats);
        }

        $outcomes = GameOutcome::select('role_id', 'win')->where('user_id', $userId)->get();

        $stats['wins'] = $outcomes->where('win', true)->count();
        $stats['losses'] = $outcomes->where('win', false)->count();

        $highestPossession = $outcomes->countBy('role_id')->sortDesc();
        $highestPossessionOccurences = $highestPossession->first();

        if ($highestPossessionOccurences !== null) {
            /** @var int $role */
            $role = $highestPossession->search($highestPossessionOccurences);

            $stats['most_possessed_role'] = [
                'role' => Roles::from($role),
                'occurences' => $highestPossessionOccurences,
            ];
        }

        $highestWinRole = $outcomes->where('win', true)->countBy('role_id')->sortDesc();
        $highestWinRoleOccurences = $highestWinRole->first();

        if ($highestWinRoleOccurences !== null) {
            /** @var int $role */
            $role = $highestWinRole->search($highestWinRoleOccurences);

            $stats['highest_win_role'] = [
                'role' => Roles::from($role),
                'occurences' => $highestWinRoleOccurences,
            ];
        }

        /** @var Statistic $userStats Because stats are created during registration */
        $userStats = Statistic::where('user_id', $userId)->first();
        $stats['win_streak'] = $userStats['win_streak'];
        $stats['longest_streak'] = $userStats['longest_streak'];

        return new JsonResponse($stats);
    }
}
