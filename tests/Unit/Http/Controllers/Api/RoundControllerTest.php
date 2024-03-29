<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\GameType;
use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\Round;
use App\Enums\State;
use App\Facades\Redis;
use App\Models\User;
use App\Services\InteractionService;
use App\Traits\MemberHelperTrait;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{
    private array $game;

    private array $secondGame;

    private array $thirdGame;

    private array $firstRound;

    private array $loopRound;

    use MemberHelperTrait;

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

        $roundList = [];

        foreach ($rounds as $index => $round) {
            $roundList[Round::cases()[$index]->value] = $round;
        }

        $this
            ->get('/api/rounds')
            ->assertOk()
            ->assertJson(['data' => ['rounds' => $roundList]]);
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
            ->get('/api/round/0')
            ->assertOk()
            ->assertJson(['data' => ['round' => $round]]);
    }

    public function testGettingOneRoundForASpecificGame()
    {
        $users = User::factory(3)->create()->toArray();

        Redis::update("game:{$this->game['id']}", function (&$game) use ($users) {
            $game['users'] = [...array_map(fn ($user) => $user['id'], $users)];
            $game['assigned_roles'] = [
                $users[0]['id'] => Role::Werewolf->value,
                $users[1]['id'] => Role::Werewolf->value,
                $users[2]['id'] => Role::Psychic->value,
            ];
        });

        $round = $this
            ->get("/api/round/0/{$this->game['id']}")
            ->assertOk()
            ->json('data.round');

        $this->assertSame($this->firstRound, $round);
    }

    public function testGettingAllRoundsForOneGame()
    {
        $users = User::factory(3)->create()->toArray();

        Redis::update("game:{$this->game['id']}", function (&$game) use ($users) {
            $game['users'] = [...array_map(fn ($user) => $user['id'], $users)];
            $game['assigned_roles'] = [
                $users[0]['id'] => Role::Werewolf->value,
                $users[1]['id'] => Role::Werewolf->value,
                $users[2]['id'] => Role::Psychic->value,
            ];
        });

        $this
            ->get("/api/rounds/{$this->game['id']}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'rounds' => [
                        Round::FirstRound->value => $this->firstRound,
                        Round::LoopRound->value => $this->loopRound,
                        Round::EndingRound->value => [
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
        $users = User::factory(2)->create()->toArray();

        Redis::update("game:{$this->secondGame['id']}", function (&$game) use ($users) {
            $game['users'] = [...array_map(fn ($user) => $user['id'], $users)];
            $game['assigned_roles'] = [
                $users[0]['id'] => Role::Werewolf->value,
                $users[1]['id'] => Role::SimpleVillager->value,
            ];
        });

        $this
            ->get("/api/round/1/{$this->secondGame['id']}")
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
                            'identifier' => State::Vote->value,
                            'raw_name' => State::Vote->stringify(),
                            'duration' => State::Vote->duration(),
                        ],
                    ],
                ],
            ]);
    }

    public function testGettingRoundInAGameWithNoSimpleWerewolf()
    {
        $users = User::factory(2)->create()->toArray();

        Redis::update("game:{$this->thirdGame['id']}", function (&$game) use ($users) {
            $game['users'] = [...array_map(fn ($user) => $user['id'], $users)];
            $game['assigned_roles'] = [
                $users[0]['id'] => Role::InfectedWerewolf->value,
            ];
        });

        Redis::set("game:{$this->thirdGame['id']}:deaths", [['user' => $users[1]['id']]]);

        $this
            ->get("/api/round/1/{$this->thirdGame['id']}")
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
                            'identifier' => State::Vote->value,
                            'raw_name' => State::Vote->stringify(),
                            'duration' => State::Vote->duration(),
                        ],
                    ],
                ],
            ]);
    }

    public function testGettingRoundWithHunter()
    {
        [$user1, $user2, $user3] = User::factory(3)->create();

        $game = $this
            ->actingAs($user1)
            ->put('/api/game', [
                'roles' => [Role::Hunter->value, Role::Werewolf->value, Role::LittleGirl->value, Role::Werewolf->value, Role::SimpleVillager->value],
            ])
            ->json('data.game');

        Redis::update("game:{$game['id']}", function (array &$game) use ($user1, $user2, $user3) {
            $game['assigned_roles'] = [
                $user1->id => Role::Werewolf,
                $user2->id => Role::Hunter,
                $user3->id => Role::LittleGirl,
            ];

            $game['users'] = [$user1->id, $user2->id, $user3->id];
        });

        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
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
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
            ]);

        $this->kill($user2->id, $game['id'], 'werewolves');

        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
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
                    'identifier' => State::Hunter->value,
                    'raw_name' => State::Hunter->stringify(),
                    'duration' => State::Hunter->duration(),
                ],
                [
                    'identifier' => State::Day->value,
                    'raw_name' => State::Day->stringify(),
                    'duration' => State::Day->duration(),
                ],
                [
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
                [
                    'identifier' => State::Hunter->value,
                    'raw_name' => State::Hunter->stringify(),
                    'duration' => State::Hunter->duration(),
                ],
            ]);

        Redis::set("game:{$game['id']}:interactions:usedActions", [InteractionAction::Shoot->value]);

        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
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
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
            ]);
    }

    public function testGettingRoundWithHunterDeadFromVote()
    {
        [$user1, $user2, $user3] = User::factory(3)->create();

        $game = $this
            ->actingAs($user1)
            ->put('/api/game', [
                'roles' => [Role::Hunter->value, Role::Werewolf->value, Role::LittleGirl->value],
            ])
            ->json('data.game');

        Redis::update("game:{$game['id']}", function (array &$game) use ($user1, $user2, $user3) {
            $game['assigned_roles'] = [
                $user1->id => Role::Werewolf,
                $user2->id => Role::Hunter,
                $user3->id => Role::LittleGirl,
            ];

            $game['users'] = [$user1->id, $user2->id, $user3->id];
        });

        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
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
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
            ]);

        Redis::update("game:{$game['id']}", function (array &$game) use ($user2) {
            $game['dead_users'][$user2->id] = [];
        });

        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
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
                    'identifier' => State::Hunter->value,
                    'raw_name' => State::Hunter->stringify(),
                    'duration' => State::Hunter->duration(),
                ],
                [
                    'identifier' => State::Day->value,
                    'raw_name' => State::Day->stringify(),
                    'duration' => State::Day->duration(),
                ],
                [
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
                [
                    'identifier' => State::Hunter->value,
                    'raw_name' => State::Hunter->stringify(),
                    'duration' => State::Hunter->duration(),
                ],
            ]);
    }

    public function testGettingRoundWithInvestigator()
    {
        [$user1] = User::factory(3)->create();
        $interactionService = app(InteractionService::class);

        $game = $this
            ->actingAs($user1)
            ->put('/api/game', [
                'roles' => [Role::SimpleVillager->value, Role::Werewolf->value, Role::Investigator->value],
            ])
            ->json('data.game');

        Redis::update("game:{$game['id']}", function (array &$game) {
            $game['assigned_roles'] = [
                'werewolf' => Role::Werewolf,
                'sv' => Role::SimpleVillager,
                'investigator' => Role::Investigator,
            ];

            $game['users'] = ['werewolf', 'sv', 'investigator'];
        });

        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
                [
                    'identifier' => State::Night->value,
                    'raw_name' => State::Night->stringify(),
                    'duration' => State::Night->duration(),
                ],
                [
                    'identifier' => State::Investigator->value,
                    'raw_name' => State::Investigator->stringify(),
                    'duration' => State::Investigator->duration(),
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
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
            ]);

        $interaction = $interactionService->create($game['id'], Interaction::Investigator, 'investigator');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $game['id'], 'investigator', 'investigator');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $game['id'], 'investigator', 'sv');
        $interactionService->close($game['id'], $interaction['id']);

        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
                [
                    'identifier' => State::Night->value,
                    'raw_name' => State::Night->stringify(),
                    'duration' => State::Night->duration(),
                ],
                [
                    'identifier' => State::Investigator->value,
                    'raw_name' => State::Investigator->stringify(),
                    'duration' => State::Investigator->duration(),
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
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
            ]);

        $interaction = $interactionService->create($game['id'], Interaction::Investigator, 'investigator');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $game['id'], 'investigator', 'werewolf');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $game['id'], 'investigator', 'sv');
        $interactionService->close($game['id'], $interaction['id']);

        // Investigator cannot compare two players anymore
        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
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
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
            ]);
    }

    public function testRoundsWithRandomCoupleAndCupid()
    {
        $user = User::factory()->createOne();

        $game = $this
            ->actingAs($user)
            ->put('/api/game', [
                'roles' => [Role::SimpleVillager->value, Role::Werewolf->value, Role::Cupid->value],
                'type' => GameType::NORMAL->value | GameType::RANDOM_COUPLE->value,
            ])
            ->json('data.game');

        Redis::update("game:{$game['id']}", function (array &$game) {
            $game['assigned_roles'] = [
                'werewolf' => Role::Werewolf,
                'sv' => Role::SimpleVillager,
                'cupid' => Role::Cupid,
            ];

            $game['users'] = ['werewolf', 'sv', 'cupid'];
        });

        $this
            ->get("/api/round/0/{$game['id']}")
            ->assertJsonPath('data.round', [
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
                [
                    'identifier' => State::Night->value,
                    'raw_name' => State::Night->stringify(),
                    'duration' => State::Night->duration(),
                ],
                [
                    'identifier' => State::RandomCoupleSelection->value,
                    'raw_name' => State::RandomCoupleSelection->stringify(),
                    'duration' => State::RandomCoupleSelection->duration(),
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
                [
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
            ]);
    }

    public function testGettingRoundsWithMayorDead()
    {
        $user = User::factory()->createOne();

        $game = $this
            ->actingAs($user)
            ->put('/api/game', [
                'roles' => [Role::SimpleVillager->value, Role::Werewolf->value],
            ])
            ->json('data.game');

        Redis::update("game:{$game['id']}", function (array &$game) {
            $game['assigned_roles'] = [
                'werewolf' => Role::Werewolf,
                'sv' => Role::SimpleVillager,
            ];

            $game['users'] = ['werewolf', 'sv'];

            $game['dead_users'] = ['sv' => []];
            $game['mayor'] = 'sv';
        });

        $this
            ->get("/api/round/1/{$game['id']}")
            ->assertJsonPath('data.round', [
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
                    'identifier' => State::MayorSuccession->value,
                    'raw_name' => State::MayorSuccession->stringify(),
                    'duration' => State::MayorSuccession->duration(),
                ],
                [
                    'identifier' => State::Day->value,
                    'raw_name' => State::Day->stringify(),
                    'duration' => State::Day->duration(),
                ],
                [
                    'identifier' => State::Vote->value,
                    'raw_name' => State::Vote->stringify(),
                    'duration' => State::Vote->duration(),
                ],
                [
                    'identifier' => State::MayorSuccession->value,
                    'raw_name' => State::MayorSuccession->stringify(),
                    'duration' => State::MayorSuccession->duration(),
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
                'roles' => [Role::Werewolf->value, Role::Werewolf->value, Role::Psychic->value, Role::SimpleVillager->value, Role::SimpleVillager->value],
            ])
            ->json('data.game');

        $this->secondGame = $this
            ->actingAs($user, 'api')
            ->put('/api/game', [
                'roles' => [Role::Werewolf->value, Role::SimpleVillager->value, Role::SimpleVillager->value, Role::SimpleVillager->value, Role::SimpleVillager->value],
            ])
            ->json('data.game');

        $this->thirdGame = $this
            ->actingAs($user, 'api')
            ->put('/api/game', [
                'roles' => [Role::SimpleVillager->value, Role::InfectedWerewolf->value, Role::SimpleVillager->value, Role::SimpleVillager->value, Role::SimpleVillager->value],
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

            [
                'identifier' => State::Mayor->value,
                'raw_name' => State::Mayor->stringify(),
                'duration' => State::Mayor->duration(),
            ],
            [
                'identifier' => State::Vote->value,
                'raw_name' => State::Vote->stringify(),
                'duration' => State::Vote->duration(),
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
    }
}
