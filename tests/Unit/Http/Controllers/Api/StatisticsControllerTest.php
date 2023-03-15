<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Role;
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
                'losses' => 0,
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
        $outcome->role_id = Role::Psychic;
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
                'losses' => 0,
                'highest_win_role' => [
                    'role' => Role::Psychic,
                    'occurences' => 1,
                ],
                'most_possessed_role' => [
                    'role' => Role::Psychic,
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

        GameOutcome::create(['user_id' => $user->id, 'role_id' => Role::SimpleVillager, 'win' => true]);
        GameOutcome::create(['user_id' => $user->id, 'role_id' => Role::Witch, 'win' => true]);
        GameOutcome::create(['user_id' => $user->id, 'role_id' => Role::Witch, 'win' => false]);
        GameOutcome::create(['user_id' => $user->id, 'role_id' => Role::Witch, 'win' => false]);
        GameOutcome::create(['user_id' => $user->id, 'role_id' => Role::SimpleVillager, 'win' => true]);

        $this
            ->actingAs($user, 'api')
            ->get('/api/stats')
            ->assertOk()
            ->assertExactJson([
                'win_streak' => 0,
                'longest_streak' => 0,
                'wins' => 3,
                'losses' => 2,
                'highest_win_role' => [
                    'role' => Role::SimpleVillager,
                    'occurences' => 2,
                ],
                'most_possessed_role' => [
                    'role' => Role::Witch,
                    'occurences' => 3,
                ],
            ]);
    }
}
