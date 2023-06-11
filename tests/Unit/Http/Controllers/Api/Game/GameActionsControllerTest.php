<?php

namespace Http\Controllers\Api\Game;

use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use Tests\TestCase;

class GameActionsControllerTest extends TestCase
{
    private array $game;

    public function testGettingAllActions()
    {
        $this
            ->get('/api/interactions/actions')
            ->assertOk()
            ->assertJson([
                'data' => ['actions' => Interaction::getActions()],
            ]);
    }

    public function testGettingActionsForPsychicInteraction()
    {
        $interaction = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interaction::Psychic->value,
            ])
            ->json('data.interaction');

        $this
            ->get("/api/interactions/actions/{$this->game['id']}/{$interaction['id']}")
            ->assertJson([
                'data' => [
                    'actions' => [
                        InteractionAction::Spectate->value,
                    ],
                ],
            ]);
    }

    public function testRetrievingWitchActionWhileOneBeingAlreadyUsed()
    {
        $interaction = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interaction::Witch->value,
            ])
            ->json('data.interaction');

        $this
            ->get("/api/interactions/actions/{$this->game['id']}/{$interaction['id']}")
            ->assertJson([
                'data' => [
                    'actions' => [
                        InteractionAction::KillPotion->value,
                        InteractionAction::RevivePotion->value,
                        InteractionAction::WitchSkip->value,
                    ],
                ],
            ]);

        Redis::set("game:{$this->game['id']}:interactions:usedActions", [InteractionAction::RevivePotion->value]);

        $this
            ->get("/api/interactions/actions/{$this->game['id']}/{$interaction['id']}")
            ->assertJson([
                'data' => [
                    'actions' => [
                        InteractionAction::KillPotion->value,
                        InteractionAction::WitchSkip->value,
                    ],
                ],
            ]);
    }

    public function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->makeOne();

        $game = $this
            ->actingAs($user, 'api')
            ->put('/api/game', [
                'users' => User::factory(2)->make()->toArray(),
                'roles' => [
                    Role::Witch->value,
                    Role::Psychic->value,
                    Role::Werewolf->value,
                    Role::Werewolf->value,
                    Role::Werewolf->value,
                ],
            ])->json('data.game');

        $this->game = $game;
    }
}
