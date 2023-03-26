<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Role;
use App\Enums\Round;
use App\Enums\State;
use App\Models\User;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{
    private array $game;

    private array $secondGame;

    private array $thirdGame;

    private array $firstRound;

    private array $secondRound;

    private array $loopRound;

    public function testGettingAllRounds()
    {
        $rounds = Round::cases();
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
            ->assertJson(['data' => ['rounds' => $rounds]]);
    }

    public function testGettingOneRound()
    {
        $round = array_map(function ($state) {
            return [
                'identifier' => $state->value,
                'raw_name' => $state->stringify(),
                'duration' => $state->duration(),
            ];
        }, Round::FirstRound->stateify());

        $this
            ->get('/api/round/1')
            ->assertOk()
            ->assertJson(['data' => ['round' => $round]]);
    }

    public function testGettingOneRoundForASpecificGame()
    {
        $round = $this
            ->get("/api/round/1/{$this->game['id']}")
            ->assertOk()
            ->json('data.round');

        $this->assertSame($this->firstRound, $round);
    }

    public function testGettingAllRoundsForOneGame()
    {
        $this
            ->get("/api/rounds/{$this->game['id']}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'rounds' => [
                        $this->firstRound,
                        $this->secondRound,
                        $this->loopRound,
                        [
                            [
                                'duration' => State::End->duration(),
                                'identifier' => State::End->value,
                                'raw_name' => State::End->stringify(),
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function testGettingRoundsForGameWithSimpleRoles()
    {
        $this
            ->get("/api/round/2/{$this->secondGame['id']}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'round' => [
                        [
                            'identifier' => State::Night->value,
                            'raw_name' => State::Night->stringify(),
                            'duration' => State::Night->duration(),
                        ],
                        [
                            'identifier' => State::Werewolf->value,
                            'raw_name' => State::Werewolf->stringify(),
                            'duration' => State::Werewolf->duration(),
                        ],
                        [
                            'identifier' => State::Day->value,
                            'raw_name' => State::Day->stringify(),
                            'duration' => State::Day->duration(),
                        ],
                        [
                            'identifier' => State::Mayor->value,
                            'raw_name' => State::Mayor->stringify(),
                            'duration' => State::Mayor->duration(),
                        ],
                    ],
                ],
            ]);
    }

    public function testGettingRoundInAGameWithNoSimpleWerewolf()
    {
        $this
            ->get("/api/round/2/{$this->thirdGame['id']}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'round' => [
                        [
                            'identifier' => State::Night->value,
                            'raw_name' => State::Night->stringify(),
                            'duration' => State::Night->duration(),
                        ],
                        [
                            'identifier' => State::Werewolf->value,
                            'raw_name' => State::Werewolf->stringify(),
                            'duration' => State::Werewolf->duration(),
                        ],
                        [
                            'identifier' => State::InfectedWerewolf->value,
                            'raw_name' => State::InfectedWerewolf->stringify(),
                            'duration' => State::InfectedWerewolf->duration(),
                        ],
                        [
                            'identifier' => State::Day->value,
                            'raw_name' => State::Day->stringify(),
                            'duration' => State::Day->duration(),
                        ],
                        [
                            'identifier' => State::Mayor->value,
                            'raw_name' => State::Mayor->stringify(),
                            'duration' => State::Mayor->duration(),
                        ],
                    ],
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
                'roles' => [Role::Werewolf->value, Role::Werewolf->value, Role::Psychic->value],
            ])
            ->json('data.game');

        $this->secondGame = $this
            ->actingAs($user, 'api')
            ->put('/api/game', [
                'roles' => [Role::Werewolf->value, Role::SimpleVillager->value],
            ])
            ->json('data.game');

        $this->thirdGame = $this
            ->actingAs($user, 'api')
            ->put('/api/game', [
                'roles' => [Role::SimpleVillager->value, Role::InfectedWerewolf->value],
            ])
            ->json('data.game');

        $this->loopRound = [
            [
                'identifier' => State::Night->value,
                'raw_name' => State::Night->stringify(),
                'duration' => State::Night->duration(),
            ],
            [
                'identifier' => State::Psychic->value,
                'raw_name' => State::Psychic->stringify(),
                'duration' => State::Psychic->duration(),
            ],
            [
                'identifier' => State::Werewolf->value,
                'raw_name' => State::Werewolf->stringify(),
                'duration' => State::Werewolf->duration(),
            ],
            [
                'identifier' => State::Day->value,
                'raw_name' => State::Day->stringify(),
                'duration' => State::Day->duration(),
            ],
        ];

        $this->secondRound = [
            ...$this->loopRound,
            [
                'identifier' => State::Mayor->value,
                'raw_name' => State::Mayor->stringify(),
                'duration' => State::Mayor->duration(),
            ],
        ];

        $this->loopRound = [
            ...$this->loopRound,
            [
                'identifier' => State::Vote->value,
                'raw_name' => State::Vote->stringify(),
                'duration' => State::Vote->duration(),
            ],
        ];

        $this->firstRound = [
            [
                'identifier' => State::Waiting->value,
                'raw_name' => State::Waiting->stringify(),
                'duration' => State::Waiting->duration(),
            ],
            [
                'identifier' => State::Starting->value,
                'raw_name' => State::Starting->stringify(),
                'duration' => State::Starting->duration(),
            ],
            [
                'identifier' => State::Roles->value,
                'raw_name' => State::Roles->stringify(),
                'duration' => State::Roles->duration(),
            ],
            ...$this->loopRound,
        ];
    }
}
