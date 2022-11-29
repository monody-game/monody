<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Enums\Roles;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
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
            ->withoutMiddleware(RestrictToDockerNetwork::class)
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
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interactions::Vote->value,
            ]);

        $interactionId = $res->json('interaction')['id'];

        $this->assertNotEmpty(Redis::get("game:{$this->game['id']}:interactions"));

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->delete('/api/interactions', [
                'gameId' => $this->game['id'],
                'id' => $interactionId,
            ])
            ->assertNoContent();

        $this->assertEmpty(Redis::get("game:{$this->game['id']}:interactions"));

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->delete('/api/interactions', [
                'gameId' => $this->game['id'],
                'id' => $interactionId,
            ])
            ->assertNotFound();

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
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
            Interactions::Witch->name => 'superWitch',
            Interactions::Psychic->name => $this->user->id,
            Interactions::Werewolves->name => json_encode([$this->secondUser->id, 'superWerewolf']),
        ];

        foreach (Interactions::cases() as $interaction) {
            $this
                ->withoutMiddleware(RestrictToDockerNetwork::class)
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
            ->withoutMiddleware(RestrictToDockerNetwork::class)
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
        Redis::set("game:{$gameId}:members", [
            ['user_id' => $this->user['id'], 'user_info' => array_merge($this->user->toArray(), ['is_dead' => true])],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);

        $res = $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
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
            ->withoutMiddleware(RestrictToDockerNetwork::class)
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
            ],
            'users' => [
                $this->user->id,
                $this->secondUser->id,
                'superWerewolf',
                'superWitch',
            ],
            'is_started' => true,
        ]);

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);

        Redis::set("game:{$this->game['id']}", $additionnalKeys);

        Redis::set("game:{$this->secondGame['id']}", array_merge(Redis::get("game:{$this->secondGame['id']}")), ['is_started' => true]);
    }
}
