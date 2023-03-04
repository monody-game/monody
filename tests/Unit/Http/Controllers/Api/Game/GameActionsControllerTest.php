<?php

namespace Http\Controllers\Api\Game;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Enums\Roles;
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
            ->assertExactJson(Interactions::getActions());
    }

    public function testGettingActionsForPsychicInteraction()
    {
        $interaction = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interactions::Psychic->value,
            ])
            ->json('interaction');

        $this
            ->get("/api/interactions/actions/{$this->game['id']}/{$interaction['id']}")
            ->assertExactJson([
                'actions' => [
                    InteractionActions::Spectate,
                ],
            ]);
    }

    public function testRetrievingWitchActionWhileOneBeingAlreadyUsed()
    {
        $interaction = $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/interactions', [
                'gameId' => $this->game['id'],
                'type' => Interactions::Witch->value,
            ])
            ->json('interaction');

        $this
            ->get("/api/interactions/actions/{$this->game['id']}/{$interaction['id']}")
            ->assertExactJson([
                'actions' => [
                    InteractionActions::KillPotion,
                    InteractionActions::RevivePotion,
                    InteractionActions::WitchSkip,
                ],
            ]);

        Redis::set("game:{$this->game['id']}:interactions:usedActions", [InteractionActions::RevivePotion->value]);

        $this
            ->get("/api/interactions/actions/{$this->game['id']}/{$interaction['id']}")
            ->assertExactJson([
                'actions' => [
                    InteractionActions::KillPotion,
                    InteractionActions::WitchSkip,
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
                    Roles::Witch->value,
                    Roles::Psychic->value,
                    Roles::Werewolf->value,
                ],
            ])->json('game');

        $this->game = $game;
    }
}
