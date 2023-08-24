<?php

namespace Actions;

use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Facades\Redis;
use App\Models\User;
use App\Services\InteractionService;
use App\Services\VoteService;
use Tests\TestCase;

class InvestigatorActionTest extends TestCase
{
    private array $game;

    public function testCreatingAndUsingInvestigatorInteraction()
    {
        $interactionService = app(InteractionService::class);
        $interaction = $interactionService->create($this->game['id'], Interaction::Investigator, 'investigator');

        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'investigator');
        $res = $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'sv');

        $this->assertTrue($res);
        $interactionService->close($this->game['id'], $interaction['id']);

        $interaction = $interactionService->create($this->game['id'], Interaction::Investigator, 'investigator');

        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'werewolf');
        $res = $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'sv');

        $this->assertFalse($res);
    }

    public function testInvestigatorCannotCompareHimTwice()
    {
        $interactionService = app(InteractionService::class);
        $interaction = $interactionService->create($this->game['id'], Interaction::Investigator, 'investigator');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'investigator');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'sv');
        $interactionService->close($this->game['id'], $interaction['id']);

        $interaction = $interactionService->create($this->game['id'], Interaction::Investigator, 'investigator');
        $this->assertSame(['not_comparable' => ['investigator']], $interaction['data']);

        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'werewolf');
        $res = $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'investigator');

        $this->assertSame($interactionService::USER_CANNOT_USE_THIS_INTERACTION, $res);
        $interactionService->close($this->game['id'], $interaction['id']);
    }

    public function testInvestigatorCannotCompareAPlayerThreeTimes()
    {
        $interactionService = app(InteractionService::class);
        $interaction = $interactionService->create($this->game['id'], Interaction::Investigator, 'investigator');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'investigator');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'sv');
        $interactionService->close($this->game['id'], $interaction['id']);

        $interaction = $interactionService->create($this->game['id'], Interaction::Investigator, 'investigator');

        $this->assertSame(['not_comparable' => ['investigator']], $interaction['data']);

        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'werewolf');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'sv');
        $interactionService->close($this->game['id'], $interaction['id']);

        $interaction = $interactionService->create($this->game['id'], Interaction::Investigator, 'investigator');

        $this->assertSame(['not_comparable' => ['investigator', 'sv']], $interaction['data']);

        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'parasite');
        $res = $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'sv');
        $this->assertSame($interactionService::USER_CANNOT_USE_THIS_INTERACTION, $res);
        $interactionService->close($this->game['id'], $interaction['id']);
    }

    public function testCancellingComparisonOnOnePlayer()
    {
        $interactionService = app(InteractionService::class);
        $interaction = $interactionService->create($this->game['id'], Interaction::Investigator, 'investigator');
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'investigator');
        $this->assertSame(['investigator' => ['investigator']], VoteService::getVotes($this->game['id']));
        $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'investigator');
        $this->assertEmpty(VoteService::getVotes($this->game['id']));
        $res = $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'werewolf');
        $this->assertNull($res);
        $res = $interactionService->call(InteractionAction::Compare, $interaction['id'], $this->game['id'], 'investigator', 'sv');
        $this->assertFalse($res);
        $interactionService->close($this->game['id'], $interaction['id']);
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

            $game['assigned_roles'] = [
                'investigator' => Role::Investigator->value, 'sv' => Role::SimpleVillager->value,
                'werewolf' => Role::Werewolf->value, 'parasite' => Role::Parasite->value,
                'whitewerewolf' => Role::WhiteWerewolf->value,
            ];
        });
    }
}
