<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Rounds;
use App\Enums\States;
use App\Models\User;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{
    public function testGettingAllRounds()
    {
        $rounds = Rounds::cases();
        $rounds = array_map(fn ($round) => $round->stateify(), $rounds);

        $this
            ->get('/api/rounds')
            ->assertOk()
            ->assertExactJson($rounds);
    }

    public function testGettingOneRound()
    {
        $this
            ->get('/api/round/1')
            ->assertOk()
            ->assertExactJson(Rounds::FirstRound->stateify());
    }

    public function testGettingOneRoundForASpecificGame()
    {
        $round = $this
            ->get("/api/round/1/{$this->game['id']}")
            ->assertOk()
            ->json();

        $this->assertSame([
            States::Waiting->value,
            States::Starting->value,
            States::Night->value,
            States::Psychic->value,
            States::Werewolf->value,
            States::Day->value,
            States::Vote->value,
        ], $round);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->game = $this
            ->actingAs($user, 'api')
            ->post('/api/game/new', [
                'roles' => [1, 3],
            ])
            ->json('game');
    }
}
