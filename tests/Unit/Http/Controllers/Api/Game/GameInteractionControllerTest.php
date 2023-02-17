<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Enums\Roles;
use App\Enums\States;
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
                'type' => Interactions::Vote->value,
            ])
            ->assertJson([
                'interaction' => [
                    'gameId' => $this->game['id'],
                    'authorizedCallers' => '*',
                    'type' => Interactions::Vote->value,
                ],
            ])
            ->assertOk();

        $interactionId = $res->json('interaction')['id'];
        $interactions = Redis::get("game:{$this->game['id']}:interactions");

        $this->assertSame([
            'gameId' => $this->game['id'],
            'id' => $interactionId,
            'authorizedCallers' => '*',
            'type' => Interactions::Vote->value,
        ], $interactions[0]);
    }

    public function testRemovingInteraction()
    {
        $res = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interactions::Vote->value,
            ]);

        $interactionId = $res->json('interaction')['id'];

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
            Interactions::Vote->name => '*',
            Interactions::Witch->name => json_encode(['superWitch']),
            Interactions::Psychic->name => json_encode([$this->user->id]),
            Interactions::Werewolves->name => json_encode(['superSickWerewolf', $this->secondUser->id, 'superWerewolf']),
            Interactions::InfectedWerewolf->name => json_encode(['superSickWerewolf']),
        ];

        foreach (Interactions::cases() as $interaction) {
            $this
                ->withoutMiddleware(RestrictToLocalNetwork::class)
                ->post('/api/interactions', [
                    'gameId' => $this->game['id'],
                    'type' => $interaction->value,
                ])
                ->assertJson([
                    'interaction' => [
                        'authorizedCallers' => $expectedAuthorized[$interaction->name],
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
                'type' => Interactions::Psychic->value,
            ])
            ->assertOk()
            ->json('interaction');

        $this
            ->actingAs($this->user, 'api')
            ->post('/api/interactions/use', [
                'gameId' => $this->game['id'],
                'id' => $res['id'],
                'targetId' => $this->secondUser->id,
                'action' => InteractionActions::Spectate->value,
            ])
            ->assertOk()
            ->assertExactJson([
                'id' => $res['id'],
                'action' => InteractionActions::Spectate->value,
                'response' => Roles::Werewolf->value,
            ]);
    }

    public function testInteractingWhileBeingDead()
    {
        $gameId = $this->game['id'];

        Redis::set(
            "game:{$gameId}",
            array_merge(Redis::get("game:{$gameId}"), ['dead_users' => [$this->user->id]])
        );

        $res = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $gameId,
                'type' => Interactions::Psychic->value,
            ])
            ->assertOk()
            ->json('interaction');

        $this
            ->actingAs($this->user)
            ->post('/api/interactions/use', [
                'gameId' => $gameId,
                'id' => $res['id'],
                'targetId' => $this->secondUser->id,
                'action' => InteractionActions::Spectate->value,
            ])
            ->assertForbidden();
    }

    public function testInteractingWithoutHavingThePermission()
    {
        $res = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interactions::Psychic->value,
            ])
            ->assertOk()
            ->json('interaction');

        $this
            ->actingAs($this->secondUser, 'api')
            ->post('/api/interactions/use', [
                'gameId' => $this->game['id'],
                'id' => $res['id'],
                'targetId' => $this->user->id,
                'action' => InteractionActions::Spectate->value,
            ])
            ->assertForbidden();
    }

    public function testGettingActions()
    {
        $this
            ->get('/api/interactions/actions')
            ->assertOk()
            ->assertExactJson(Interactions::getActions());
    }

    public function testGettingActionsForOneInteraction()
    {
        $interaction = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interactions::Witch->value,
            ])
            ->assertJson([
                'interaction' => [
                    'gameId' => $this->game['id'],
                    'authorizedCallers' => json_encode(['superWitch']),
                    'type' => Interactions::Witch->value,
                ],
            ])
            ->assertOk()
            ->json('interaction');

        $this
            ->get("/api/interactions/actions/{$interaction['gameId']}/{$interaction['id']}")
            ->assertOk()
            ->assertExactJson([
                'actions' => [
                    InteractionActions::KillPotion,
                    InteractionActions::RevivePotion,
                    InteractionActions::WitchSkip,
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
                    'roles' => [1, 1, 3, 4],
                ])
                ->json('game');

        $this->secondGame =
            $this
                ->actingAs($this->user, 'api')
                ->put('/api/game', [
                    'roles' => [1, 2],
                ])
                ->json('game');

        $this->user->current_game = $this->game['id'];
        $this->user->save();
        $this->secondUser->current_game = $this->game['id'];
        $this->secondUser->save();

        $additionnalKeys = array_merge($this->game, [
            'assigned_roles' => [
                $this->secondUser->id => 1,
                'superWerewolf' => 1,
                $this->user->id => 3,
                'superWitch' => 4,
                'superSickWerewolf' => Roles::InfectedWerewolf,
            ],
            'users' => [
                $this->user->id,
                $this->secondUser->id,
                'superWerewolf',
                'superWitch',
                'superSickWerewolf',
            ],
            'is_started' => true,
        ]);

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
            ['user_id' => 'superWerewolf', 'user_info' => []],
            ['user_id' => 'superWitch', 'user_info' => []],
            ['user_id' => 'superSickWerewolf', 'user_info' => []],
        ]);

        Redis::set("game:{$this->game['id']}", $additionnalKeys);

        Redis::set("game:{$this->secondGame['id']}", array_merge(Redis::get("game:{$this->secondGame['id']}")), ['is_started' => true]);

        Redis::set("game:{$this->game['id']}:state", [
            'status' => States::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => States::Vote->duration(),
        ]);

        Redis::set("game:{$this->secondGame['id']}:state", [
            'status' => States::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => States::Vote->duration(),
        ]);
    }
}
