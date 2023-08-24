<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\State;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class GameInteractionControllerTest extends TestCase
{
    private array $game;

    private array $secondGame;

    private User $user;

    private User $secondUser;

    public function testCreatingInteraction()
    {
        $res = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interaction::Vote->value,
            ])
            ->assertJson([
                'data' => [
                    'interaction' => [
                        'gameId' => $this->game['id'],
                        'authorizedCallers' => '*',
                        'type' => Interaction::Vote->value,
                    ],
                ],
            ])
            ->assertOk();

        $interactionId = $res->json('data.interaction')['id'];
        $interactions = Redis::get("game:{$this->game['id']}:interactions");

        $this->assertSame([
            'gameId' => $this->game['id'],
            'id' => $interactionId,
            'authorizedCallers' => '*',
            'type' => Interaction::Vote->value,
        ], $interactions[0]);
    }

    public function testRemovingInteraction()
    {
        $res = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interaction::Vote->value,
            ]);

        $interactionId = $res->json('data.interaction')['id'];

        $this->assertNotEmpty(Redis::get("game:{$this->game['id']}:interactions"));

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/interactions', [
                'gameId' => $this->game['id'],
                'id' => $interactionId,
            ])
            ->assertNoContent();

        $this->assertEmpty(Redis::get("game:{$this->game['id']}:interactions"));

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/interactions', [
                'gameId' => $this->game['id'],
                'id' => $interactionId,
            ])
            ->assertNotFound();

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->delete('/api/interactions', [
                'gameId' => $this->secondGame['id'],
                'id' => $interactionId,
            ])
            ->assertNotFound();
    }

    public function testGettingCorrectCallAuthorization()
    {
        $expectedAuthorized = [
            Interaction::Vote->name => '*',
            Interaction::Mayor->name => '*',
            Interaction::Witch->name => json_encode(['superWitch']),
            Interaction::Psychic->name => json_encode([$this->user->id]),
            Interaction::Werewolves->name => json_encode(['surlyWerewolf', 'whiteWerewolf', 'superSickWerewolf', $this->secondUser->id, 'superWerewolf']),
            Interaction::InfectedWerewolf->name => json_encode(['superSickWerewolf']),
            Interaction::WhiteWerewolf->name => json_encode(['whiteWerewolf']),
            Interaction::Angel->name => json_encode(['superAngel']),
            Interaction::SurlyWerewolf->name => json_encode(['surlyWerewolf']),
            Interaction::Parasite->name => json_encode(['parasite']),
            Interaction::Cupid->name => json_encode(['cupid']),
            Interaction::Guard->name => json_encode(['guard']),
            Interaction::Hunter->name => json_encode(['hunter']),
            Interaction::Investigator->name => json_encode(['investigator']),
        ];

        foreach (Interaction::cases() as $interaction) {
            $this
                ->withoutMiddleware(RestrictToLocalNetwork::class)
                ->post('/api/interactions', [
                    'gameId' => $this->game['id'],
                    'type' => $interaction->value,
                ])
                ->assertJson([
                    'data' => [
                        'interaction' => [
                            'authorizedCallers' => $expectedAuthorized[$interaction->name],
                        ],
                    ],
                ]);
        }
    }

    public function testInteracting()
    {
        $res = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interaction::Psychic->value,
            ])
            ->assertOk()
            ->json('data.interaction');

        $this
            ->actingAs($this->user, 'api')
            ->post('/api/interactions/use', [
                'gameId' => $this->game['id'],
                'id' => $res['id'],
                'targetId' => $this->secondUser->id,
                'action' => InteractionAction::Spectate->value,
            ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'interaction' => [
                        'id' => $res['id'],
                        'action' => InteractionAction::Spectate->value,
                        'response' => Role::Werewolf->value,
                    ],
                ],
            ]);
    }

    public function testInteractingWhileBeingDead()
    {
        $gameId = $this->game['id'];

        Redis::set(
            "game:$gameId",
            array_merge(Redis::get("game:$gameId"), ['dead_users' => [$this->user->id => []]])
        );

        $res = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $gameId,
                'type' => Interaction::Psychic->value,
            ])
            ->assertOk()
            ->json('data.interaction');

        $this
            ->actingAs($this->user)
            ->post('/api/interactions/use', [
                'gameId' => $gameId,
                'id' => $res['id'],
                'targetId' => $this->secondUser->id,
                'action' => InteractionAction::Spectate->value,
            ])
            ->assertForbidden();
    }

    public function testInteractingWithoutHavingThePermission()
    {
        $res = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interaction::Psychic->value,
            ])
            ->assertOk()
            ->json('data.interaction');

        $this
            ->actingAs($this->secondUser, 'api')
            ->post('/api/interactions/use', [
                'gameId' => $this->game['id'],
                'id' => $res['id'],
                'targetId' => $this->user->id,
                'action' => InteractionAction::Spectate->value,
            ])
            ->assertForbidden();
    }

    public function testGettingActions()
    {
        $this
            ->get('/api/interactions/actions')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'actions' => Interaction::getActions(),
                ],
            ]);
    }

    public function testGettingActionsForOneInteraction()
    {
        $interaction = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interaction::Witch->value,
            ])
            ->assertJson([
                'data' => [
                    'interaction' => [
                        'gameId' => $this->game['id'],
                        'authorizedCallers' => json_encode(['superWitch']),
                        'type' => Interaction::Witch->value,
                    ],
                ],
            ])
            ->assertOk()
            ->json('data.interaction');

        $this
            ->get("/api/interactions/actions/{$interaction['gameId']}/{$interaction['id']}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'actions' => [
                        InteractionAction::KillPotion->value,
                        InteractionAction::RevivePotion->value,
                        InteractionAction::WitchSkip->value,
                    ],
                ],
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        [$this->user, $this->secondUser] = User::factory(2)->create();
        $this->game =
            $this
                ->actingAs($this->user, 'api')
                ->put('/api/game', [
                    'roles' => [1, 1, 3, 4, 5],
                ])
                ->json('data.game');

        $this->secondGame =
            $this
                ->actingAs($this->user, 'api')
                ->put('/api/game', [
                    'roles' => [1, 2, 3, 4, 5],
                ])
                ->json('data.game');

        $this->user->current_game = $this->game['id'];
        $this->user->save();
        $this->secondUser->current_game = $this->game['id'];
        $this->secondUser->save();

        $additionnalKeys = array_merge($this->game, [
            'assigned_roles' => [
                $this->secondUser->id => Role::Werewolf,
                'superWerewolf' => Role::Werewolf,
                $this->user->id => Role::Psychic,
                'superWitch' => Role::Witch,
                'superSickWerewolf' => Role::InfectedWerewolf,
                'whiteWerewolf' => Role::WhiteWerewolf,
                'superAngel' => Role::Angel,
                'surlyWerewolf' => Role::SurlyWerewolf,
                'parasite' => Role::Parasite,
                'cupid' => Role::Cupid,
                'guard' => Role::Guard,
                'hunter' => Role::Hunter,
                'investigator' => Role::Investigator,
            ],
            'users' => [
                $this->user->id,
                $this->secondUser->id,
                'superWerewolf',
                'superWitch',
                'superSickWerewolf',
                'whiteWerewolf',
                'superAngel',
                'surlyWerewolf',
                'parasite',
                'cupid',
                'guard',
                'hunter',
                'investigator',
            ],
            'is_started' => true,
        ]);

        Redis::set("game:{$this->game['id']}", $additionnalKeys);

        Redis::set("game:{$this->secondGame['id']}", array_merge(Redis::get("game:{$this->secondGame['id']}"), ['is_started' => true]));

        Redis::set("game:{$this->game['id']}:state", [
            'status' => State::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => State::Vote->duration(),
        ]);

        Redis::set("game:{$this->secondGame['id']}:state", [
            'status' => State::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => State::Vote->duration(),
        ]);
    }
}
