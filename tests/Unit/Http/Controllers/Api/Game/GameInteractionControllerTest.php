<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\GameInteractions;
use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
use Tests\TestCase;

class GameInteractionControllerTest extends TestCase
{
    private array $game;

    private User $user;

    private User $secondUser;

    public function testCreatingInteraction()
    {
        $res = $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => GameInteractions::Vote->value,
            ])
            ->assertJson([
                'interaction' => [
                    'gameId' => $this->game['id'],
                    'authorizedCallers' => '*',
                    'type' => GameInteractions::Vote->value,
                ],
            ])
            ->assertOk();

        $interactionId = $res->json('interaction')['interactionId'];
        $interactions = Redis::get("game:{$this->game['id']}:interactions");

        $this->assertSame([
            'gameId' => $this->game['id'],
            'interactionId' => $interactionId,
            'authorizedCallers' => '*',
            'type' => GameInteractions::Vote->value,
        ], $interactions[0]);
    }

    public function testRemovingInteraction()
    {
        $res = $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => GameInteractions::Vote->value,
            ]);

        $interactionId = $res->json('interaction')['interactionId'];

        $this->assertNotEmpty(Redis::get("game:{$this->game['id']}:interactions"));

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->delete('/api/interactions', [
                'gameId' => $this->game['id'],
                'interactionId' => $interactionId,
            ])
            ->assertNoContent();

        $this->assertEmpty(Redis::get("game:{$this->game['id']}:interactions"));
    }

    public function testGettingCorrectCallAuthorization()
    {
        $expectedAuthorized = [
            GameInteractions::Vote->name => '*',
            GameInteractions::Witch->name => 'superWitch',
            GameInteractions::Psychic->name => $this->user->id,
            GameInteractions::Werewolves->name => json_encode([$this->secondUser->id, 'superWerewolf']),
        ];

        foreach (GameInteractions::cases() as $interaction) {
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
                'type' => GameInteractions::Psychic->value,
            ])
            ->assertOk()
            ->json('interaction');

        $this
            ->actingAs($this->user, 'api')
            ->post('/api/interactions/use', [
                'gameId' => $this->game['id'],
                'interactionId' => $res['interactionId'],
                'targetId' => $this->secondUser->id,
                'interaction' => InteractionActions::Spectate->value,
            ])
            ->assertOk()
            ->assertExactJson([
                'interactionId' => $res['interactionId'],
                'interaction' => InteractionActions::Spectate->value,
                'response' => Roles::Werewolf->value,
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        [$this->user, $this->secondUser] = User::factory(2)->create();
        $this->game =
            $this
            ->actingAs($this->user, 'api')
            ->post('/api/game/new', [
                'roles' => [1, 1, 3, 4],
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
            'is_started' => true,
        ]);

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);

        Redis::set("game:{$this->game['id']}", $additionnalKeys);
    }
}
