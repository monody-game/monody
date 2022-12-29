<?php

namespace Tests\Unit\Services;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Enums\Roles;
use App\Facades\Redis;
use App\Models\User;
use App\Services\InteractionService;
use App\Traits\MemberHelperTrait;
use Tests\TestCase;

class InteractionServiceTest extends TestCase
{
    use MemberHelperTrait;

    private InteractionService $service;

    private array $game;

    private User $user;

    private User $witch;

    private User $psychic;

    private User $werewolf;

    public function testCreatingAnInteraction()
    {
        $expectedInteraction = [
            'gameId' => 'test',
            'authorizedCallers' => '*',
            'type' => 'vote',
        ];

        $interaction = $this->service->create('test', Interactions::Vote);

        $redisInteraction = Redis::get('game:test:interactions');
        $expectedInteraction['id'] = $interaction['id'];

        $this->assertSame(sort($expectedInteraction), sort($interaction));
        $this->assertSame(sort($expectedInteraction), sort($redisInteraction[0]));
    }

    public function testEndingInteraction()
    {
        $interaction = $this->service->create('otherGame', Interactions::Vote);
        $this->assertNull($this->service->close('otherGame', $interaction['id']));

        $this->assertEmpty(Redis::get('game:otherGame:interactions'));
    }

    public function testEndingUnexistantInteraction()
    {
        $this->service->create('otherGame', Interactions::Vote);
        $res = $this->service->close('otherGame', 'unexistantInteraction');
        $this->assertSame($this->service::INTERACTION_DOES_NOT_EXISTS, $res);
        $res = $this->service->close('unexistingGame', 'unexistantInteraction');
        $this->assertSame($this->service::NOT_ANY_INTERACTION_STARTED, $res);
    }

    public function testUsingInteraction()
    {
        // Psychic
        $id = $this->service->create($this->game['id'], Interactions::Psychic)['id'];
        $spectate = $this->service->call(InteractionActions::Spectate, $id, $this->psychic->id, $this->user->id);
        $this->assertSame(Roles::SimpleVillager->value, $spectate);

        // Witch
        $id = $this->service->create($this->game['id'], Interactions::Witch)['id'];
        $witchNone = $this->service->call(InteractionActions::WitchSkip, $id, $this->witch->id, '');
        $this->assertNull($witchNone);

        $id = $this->service->create($this->game['id'], Interactions::Witch)['id'];
        $this->service->call(InteractionActions::KillPotion, $id, $this->witch->id, $this->user->id);
        $this->assertFalse($this->alive($this->user->id, $this->game['id']));

        $id = $this->service->create($this->game['id'], Interactions::Witch)['id'];
        $this->service->call(InteractionActions::RevivePotion, $id, $this->witch->id, $this->user->id);
        $this->assertTrue($this->alive($this->user->id, $this->game['id']));

        // Werewolves
        $id = $this->service->create($this->game['id'], Interactions::Werewolves)['id'];
        $this->service->call(InteractionActions::Kill, $id, $this->werewolf->id, $this->psychic->id);
        $this->service->close($this->game['id'], $id);
        $this->assertFalse($this->alive($this->psychic->id, $this->game['id']));

        // Vote
        $id = $this->service->create($this->game['id'], Interactions::Vote)['id'];
        $vote = $this->service->call(InteractionActions::Vote, $id, $this->user->id, $this->witch->id);
        $this->assertSame([$this->witch->id => [$this->user->id]], $vote);
        $vote = $this->service->call(InteractionActions::Vote, $id, $this->user->id, $this->witch->id);
        $this->assertSame([], $vote);
    }

    public function testBeingUnAllowedToUseInteraction()
    {
        $id = $this->service->create($this->game['id'], Interactions::Witch)['id'];
        $this->assertSame(
            $this->service::USER_CANNOT_USE_THIS_INTERACTION,
            $this->service->call(InteractionActions::WitchSkip, $id, $this->werewolf->id, '')
        );
    }

    public function testUsingAOneUseInteraction()
    {
        $id = $this->service->create($this->game['id'], Interactions::Psychic)['id'];
        $res = $this->service->call(InteractionActions::Spectate, $id, $this->psychic->id, $this->werewolf->id);
        $this->assertSame(Roles::Werewolf->value, $res);
        $res = $this->service->call(InteractionActions::Spectate, $id, $this->psychic->id, $this->witch->id);
        $this->assertSame($this->service::USER_CANNOT_USE_THIS_INTERACTION, $res);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->witch, $this->psychic, $this->werewolf] = User::factory(4)->create();
        $users = [$this->user, $this->witch, $this->psychic, $this->werewolf];
        $this->service = new InteractionService();

        $this->game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'roles' => [1, 2, 3, 4],
            ])
            ->json('game');

        $additionnalKeys = array_merge($this->game, [
            'assigned_roles' => [
                $this->werewolf->id => 1,
                $this->user->id => 2,
                $this->psychic->id => 3,
                $this->witch->id => 4,
            ],
            'is_started' => true,
        ]);

        $members = [];

        foreach ($users as $user) {
            $user->current_game = $this->game['id'];
            $user->save();
            $members[] = ['user_id' => $user->id, 'user_info' => $user];
        }

        Redis::set("game:{$this->game['id']}:members", $members);
        Redis::set("game:{$this->game['id']}", $additionnalKeys);
    }
}
