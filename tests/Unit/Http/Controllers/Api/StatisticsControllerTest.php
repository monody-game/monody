<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Roles;
use App\Models\GameOutcome;
use App\Models\Statistic;
use App\Models\User;
use Tests\TestCase;

class StatisticsControllerTest extends TestCase
{
    public function testRetrievingDefaultUserStats()
    {
        $user = User::factory()->create();

        $this
            ->get("/api/stats/{$user->id}")
            ->assertOk()
            ->assertExactJson([
                'win_streak' => 0,
                'longest_streak' => 0,
                'wins' => 0,
                'looses' => 0,
                'highest_win_role' => null,
                'most_possessed_role' => null,
            ]);
    }

    public function testRetrievingStatsWithOneWin()
    {
        $user = User::factory()->create();

        $stats = new Statistic();
        $stats->user_id = $user->id;
        $stats->save();

        $outcome = new GameOutcome();
        $outcome->user_id = $user->id;
        $outcome->role_id = Roles::Psychic;
        $outcome->win = true;
        $outcome->save();

        $this
            ->actingAs($user, 'api')
            ->get('/api/stats')
            ->assertOk()
            ->assertExactJson([
                'win_streak' => 0,
                'longest_streak' => 0,
                'wins' => 1,
                'looses' => 0,
                'highest_win_role' => [
                    'role' => Roles::Psychic,
                    'occurences' => 1,
                ],
                'most_possessed_role' => [
                    'role' => Roles::Psychic,
                    'occurences' => 1,
                ],
            ]);
    }

    public function testRetrievingStatsWithMultipleGameOutcomes()
    {
        $user = User::factory()->create();

        $stats = new Statistic();
        $stats->user_id = $user->id;
        $stats->save();

        GameOutcome::create(['user_id' => $user->id, 'role_id' => Roles::SimpleVillager, 'win' => true]);
        GameOutcome::create(['user_id' => $user->id, 'role_id' => Roles::Witch, 'win' => true]);
        GameOutcome::create(['user_id' => $user->id, 'role_id' => Roles::Witch, 'win' => false]);
        GameOutcome::create(['user_id' => $user->id, 'role_id' => Roles::Witch, 'win' => false]);
        GameOutcome::create(['user_id' => $user->id, 'role_id' => Roles::SimpleVillager, 'win' => true]);

        $this
            ->actingAs($user, 'api')
            ->get('/api/stats')
            ->assertOk()
            ->assertExactJson([
                'win_streak' => 0,
                'longest_streak' => 0,
                'wins' => 3,
                'looses' => 2,
                'highest_win_role' => [
                    'role' => Roles::SimpleVillager,
                    'occurences' => 2,
                ],
                'most_possessed_role' => [
                    'role' => Roles::Witch,
                    'occurences' => 3,
                ],
            ]);
    }
}
