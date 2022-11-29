<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Rounds;
use App\Enums\States;
use App\Models\User;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{
    private array $game;

    private array $secondGame;

    private array $firstRound;

    private array $secondRound;

    public function testGettingAllRounds()
    {
        $rounds = Rounds::cases();
        $rounds = array_map(function ($round) {
            $states = $round->stateify();

            return array_map(function ($state) {
                return [
                    'identifier' => $state->value,
                    'raw_name' => $state->stringify(),
                    'duration' => $state->duration(),
                ];
            }, $states);
        }, $rounds);

        $this
            ->get('/api/rounds')
            ->assertOk()
            ->assertExactJson($rounds);
    }

    public function testGettingOneRound()
    {
        $round = array_map(function ($state) {
            return [
                'identifier' => $state,
                'raw_name' => $state->stringify(),
                'duration' => $state->duration(),
            ];
        }, Rounds::FirstRound->stateify());

        $this
            ->get('/api/round/1')
            ->assertOk()
            ->assertExactJson($round);
    }

    public function testGettingOneRoundForASpecificGame()
    {
        $round = $this
            ->get("/api/round/1/{$this->game['id']}")
            ->assertOk()
            ->json();

        $this->assertSame($this->firstRound, $round);
    }

    public function testGettingAllRoundsForOneGame()
    {
        $this
            ->get("/api/rounds/{$this->game['id']}")
            ->assertOk()
            ->assertExactJson([
                $this->firstRound,
                $this->secondRound,
                $this->secondRound,
            ]);
    }

    public function testGettingRoundsForGameWithSimpleRoles()
    {
        $rounds = $this
            ->get("/api/round/2/{$this->secondGame['id']}")
            ->assertOk()
            ->assertExactJson([
                [
                    'identifier' => States::Night->value,
                    'raw_name' => States::Night->stringify(),
                    'duration' => States::Night->duration(),
                ],
                [
                    'identifier' => States::Werewolf->value,
                    'raw_name' => States::Werewolf->stringify(),
                    'duration' => States::Werewolf->duration(),
                ],
                [
                    'identifier' => States::Day->value,
                    'raw_name' => States::Day->stringify(),
                    'duration' => States::Day->duration(),
                ],
                [
                    'identifier' => States::Vote->value,
                    'raw_name' => States::Vote->stringify(),
                    'duration' => States::Vote->duration(),
                ],
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->game = $this
            ->actingAs($user, 'api')
            ->put('/api/game', [
                'roles' => [1, 1, 3],
            ])
            ->json('game');

        $this->secondGame = $this
            ->actingAs($user, 'api')
            ->put('/api/game', [
                'roles' => [1, 2],
            ])
            ->json('game');

        $this->secondRound = [
            [
                'identifier' => States::Night->value,
                'raw_name' => States::Night->stringify(),
                'duration' => States::Night->duration(),
            ],
            [
                'identifier' => States::Psychic->value,
                'raw_name' => States::Psychic->stringify(),
                'duration' => States::Psychic->duration(),
            ],
            [
                'identifier' => States::Werewolf->value,
                'raw_name' => States::Werewolf->stringify(),
                'duration' => States::Werewolf->duration(),
            ],
            [
                'identifier' => States::Day->value,
                'raw_name' => States::Day->stringify(),
                'duration' => States::Day->duration(),
            ],
            [
                'identifier' => States::Vote->value,
                'raw_name' => States::Vote->stringify(),
                'duration' => States::Vote->duration(),
            ],
        ];

        $this->firstRound = [
            [
                'identifier' => States::Waiting->value,
                'raw_name' => States::Waiting->stringify(),
                'duration' => States::Waiting->duration(),
            ],
            [
                'identifier' => States::Starting->value,
                'raw_name' => States::Starting->stringify(),
                'duration' => States::Starting->duration(),
            ],
            ...$this->secondRound,
        ];
    }
}
