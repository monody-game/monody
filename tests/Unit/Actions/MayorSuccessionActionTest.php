<?php

namespace Actions;

use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Events\MayorElected;
use App\Facades\Redis;
use App\Models\User;
use App\Services\InteractionService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MayorSuccessionActionTest extends TestCase
{
    private array $game;

    public function testDesignatingANewMayor()
    {
        Event::fake();

        $interactionService = app(InteractionService::class);
        $interaction = $interactionService->create($this->game['id'], Interaction::MayorSuccession, 'investigator');

        $interactionService->call(InteractionAction::Designate, $interaction['id'], $this->game['id'], 'investigator', 'sv');

        Event::assertDispatched(function (MayorElected $event) {
            return ((array) $event)['payload'] === [
                'gameId' => $this->game['id'],
                'mayor' => 'sv',
            ];
        });

        $interactionService->close($this->game['id'], $interaction['id']);

        $game = Redis::get("game:{$this->game['id']}");

        $this->assertSame('sv', $game['mayor']);
    }

    public function testDesignatingADeadUser()
    {
        Event::fake();

        $interactionService = app(InteractionService::class);
        $interaction = $interactionService->create($this->game['id'], Interaction::MayorSuccession, 'investigator');

        $code = $interactionService->call(InteractionAction::Designate, $interaction['id'], $this->game['id'], 'investigator', 'werewolf');
        Event::assertNotDispatched(MayorElected::class);

        $this->assertSame(InteractionService::USER_CANNOT_USE_THIS_INTERACTION, $code);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->game = $this
            ->actingAs($user)
            ->put('/api/game', [
                'roles' => [Role::Investigator->value, Role::SimpleVillager->value, Role::Werewolf->value, Role::Parasite->value, Role::WhiteWerewolf->value],
                'users' => ['investigator', 'sv', 'werewolf', 'parasite', 'whitewerewolf'],
            ])
            ->json('data.game');

        Redis::update("game:{$this->game['id']}", function (array &$game) {
            $game['is_started'] = true;
            $game['mayor'] = 'investigator';
            $game['dead_users'] = ['werewolf' => []];

            $game['assigned_roles'] = [
                'investigator' => Role::Investigator->value, 'sv' => Role::SimpleVillager->value,
                'werewolf' => Role::Werewolf->value, 'parasite' => Role::Parasite->value,
                'whitewerewolf' => Role::WhiteWerewolf->value,
            ];
        });
    }
}
