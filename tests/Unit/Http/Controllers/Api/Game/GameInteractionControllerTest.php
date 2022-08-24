<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\GameInteractions;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
use Tests\TestCase;

class GameInteractionControllerTest extends TestCase
{
    private array $game;

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
            GameInteractions::Psychic->name => 'superPsychic',
            GameInteractions::Werewolves->name => json_encode(['superWerewolf', 'superWerewolf2']),
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

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->make();
        $this->game =
            $this
            ->actingAs($user, 'api')
            ->post('/api/game/new', [
                'roles' => [1 => 2, 3 => 1, 4 => 1],
            ])
            ->json('game');

        $additionnalKeys = array_merge($this->game, [
            'assigned_roles' => [
                'superWerewolf' => 1,
                'superWerewolf2' => 1,
                'superPsychic' => 3,
                'superWitch' => 4,
            ],
            'is_started' => true,
        ]);

        Redis::set("game:{$this->game['id']}", $additionnalKeys);
    }
}
