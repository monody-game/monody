<?php

namespace Tests\Unit\Services;

use App\Enums\Interaction;
use App\Enums\InteractionAction;
use App\Enums\Role;
use App\Enums\State;
use App\Events\MayorElected;
use App\Facades\Redis;
use App\Models\User;
use App\Services\InteractionService;
use App\Services\VoteService;
use App\Traits\MemberHelperTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
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

    private User $infectedWerewolf;

    private User $angel;

    public function testCreatingAnInteraction()
    {
        $expectedInteraction = [
            'gameId' => 'test',
            'authorizedCallers' => '*',
            'type' => 'vote',
        ];

        $interaction = $this->service->create('test', Interaction::Vote);

        $redisInteraction = Redis::get('game:test:interactions');
        $expectedInteraction['id'] = $interaction['id'];

        $this->assertSame(sort($expectedInteraction), sort($interaction));
        $this->assertSame(sort($expectedInteraction), sort($redisInteraction[0]));
    }

    public function testEndingInteraction()
    {
        $interaction = $this->service->create($this->game['id'], Interaction::Vote);
        $this->assertNull($this->service->close($this->game['id'], $interaction['id']));

        $this->assertEmpty(Redis::get('game:otherGame:interactions'));
    }

    public function testEndingUnexistantInteraction()
    {
        $this->service->create('otherGame', Interaction::Vote);
        $res = $this->service->close('otherGame', 'unexistantInteraction');
        $this->assertSame($this->service::INTERACTION_DOES_NOT_EXISTS, $res);
        $res = $this->service->close('unexistingGame', 'unexistantInteraction');
        $this->assertSame($this->service::NOT_ANY_INTERACTION_STARTED, $res);
    }

    public function testUsingInteraction()
    {
        // Psychic
        $id = $this->service->create($this->game['id'], Interaction::Psychic)['id'];
        $spectate = $this->service->call(InteractionAction::Spectate, $id, $this->psychic->id, $this->user->id);
        $this->assertSame(Role::SimpleVillager->value, $spectate);

        // Witch
        $id = $this->service->create($this->game['id'], Interaction::Witch)['id'];
        $witchNone = $this->service->call(InteractionAction::WitchSkip, $id, $this->witch->id, '');
        $this->assertNull($witchNone);

        $id = $this->service->create($this->game['id'], Interaction::Witch)['id'];
        $this->service->call(InteractionAction::KillPotion, $id, $this->witch->id, $this->user->id);
        $this->assertFalse($this->alive($this->user->id, $this->game['id']));

        $interaction = $this->service->create($this->game['id'], Interaction::Witch, $this->witch->id, [$this->user->id]);
        $id = $interaction['id'];

        $this->assertSame([
            'gameId' => $this->game['id'],
            'id' => $id,
            'authorizedCallers' => $this->witch->id,
            'type' => 'witch',
            'data' => [
                $this->user->id,
            ],
        ], $interaction);
        $this->service->call(InteractionAction::RevivePotion, $id, $this->witch->id, $this->user->id);
        $this->assertTrue($this->alive($this->user->id, $this->game['id']));

        // Werewolves
        $id = $this->service->create($this->game['id'], Interaction::Werewolves)['id'];
        $this->service->call(InteractionAction::Kill, $id, $this->werewolf->id, $this->psychic->id);
        $this->service->close($this->game['id'], $id);
        $this->assertFalse($this->alive($this->psychic->id, $this->game['id']));

        // Infected Werewolf
        $id = $this->service->create($this->game['id'], Interaction::InfectedWerewolf)['id'];
        $this->service->call(InteractionAction::InfectedSkip, $id, $this->infectedWerewolf->id, $this->psychic->id);
        $this->service->close($this->game['id'], $id);
        $this->assertFalse($this->alive($this->psychic->id, $this->game['id']));

        $id = $this->service->create($this->game['id'], Interaction::InfectedWerewolf)['id'];
        $this->service->call(InteractionAction::Infect, $id, $this->infectedWerewolf->id, $this->psychic->id);
        $this->service->close($this->game['id'], $id);
        $this->assertTrue($this->alive($this->psychic->id, $this->game['id']));
        $this->assertContains($this->psychic->id, Redis::get("game:{$this->game['id']}")['werewolves']);
        $this->assertEmpty(Redis::get("game:{$this->game['id']}:deaths"));

        // Vote
        $id = $this->service->create($this->game['id'], Interaction::Vote)['id'];
        $vote = $this->service->call(InteractionAction::Vote, $id, $this->user->id, $this->witch->id);
        $this->assertSame([$this->witch->id => [$this->user->id]], $vote);
        $vote = $this->service->call(InteractionAction::Vote, $id, $this->user->id, $this->witch->id);
        $this->assertSame([], $vote);

        // Guard
        $id = $this->service->create($this->game['id'], Interaction::Guard)['id'];
        $this->service->call(InteractionAction::Guard, $id, $this->guard->id, $this->witch->id);
        $this->assertSame($this->witch->id, Redis::get("game:{$this->game['id']}")['guarded']);
        $this->service->close($this->game['id'], $id);
        $id = $this->service->create($this->game['id'], Interaction::Guard)['id'];
        $res = $this->service->call(InteractionAction::Guard, $id, $this->guard->id, $this->witch->id);
        $this->assertSame($this->service::USER_CANNOT_USE_THIS_INTERACTION, $res);
        $this->service->call(InteractionAction::Guard, $id, $this->guard->id, $this->psychic->id);
        $this->assertSame($this->psychic->id, Redis::get("game:{$this->game['id']}")['guarded']);
    }

    public function testBeingUnAllowedToUseInteraction()
    {
        $id = $this->service->create($this->game['id'], Interaction::Witch)['id'];
        $this->assertSame(
            $this->service::USER_CANNOT_USE_THIS_INTERACTION,
            $this->service->call(InteractionAction::WitchSkip, $id, $this->werewolf->id, '')
        );
    }

    public function testUsingAOneUseInteraction()
    {
        $id = $this->service->create($this->game['id'], Interaction::Psychic)['id'];
        $res = $this->service->call(InteractionAction::Spectate, $id, $this->psychic->id, $this->werewolf->id);
        $this->assertSame(Role::Werewolf->value, $res);
        $res = $this->service->call(InteractionAction::Spectate, $id, $this->psychic->id, $this->witch->id);
        $this->assertSame($this->service::USER_CANNOT_USE_THIS_INTERACTION, $res);
    }

    public function testElectingAMayor()
    {
        Event::fake();

        $userId = $this->werewolf->id;
        $gameId = $this->game['id'];

        $id = $this->service->create($gameId, Interaction::Mayor)['id'];
        $this->service->call(InteractionAction::Elect, $id, $this->psychic->id, $userId);
        $this->service->call(InteractionAction::Elect, $id, $userId, $userId);
        $this->service->call(InteractionAction::Elect, $id, $this->witch->id, $userId);
        $this->service->close($gameId, $id);

        Event::assertDispatched(function (MayorElected $event) use ($userId, $gameId) {
            return ((array) $event)['payload'] === [
                'gameId' => $gameId,
                'mayor' => $userId,
            ];
        });

        $game = Redis::get("game:{$this->game['id']}");

        $this->assertArrayHasKey('mayor', $game);
        $this->assertSame($userId, $game['mayor']);
    }

    public function testShouldSkipTimer()
    {
        $id = $this->service->create($this->game['id'], Interaction::Psychic)['id'];
        $this->service->call(InteractionAction::Spectate, $id, $this->psychic->id, $this->werewolf->id);
        $this->assertTrue($this->service->shouldSkipTime($id, $this->game['id']));

        $id = $this->service->create($this->game['id'], Interaction::Vote)['id'];
        $this->service->call(InteractionAction::Vote, $id, $this->psychic->id, $this->werewolf->id);
        $this->assertFalse($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionAction::Vote, $id, $this->user->id, $this->werewolf->id);
        $this->assertFalse($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionAction::Vote, $id, $this->infectedWerewolf->id, $this->werewolf->id);
        $this->assertTrue($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionAction::Vote, $id, $this->witch->id, $this->werewolf->id);
        $this->assertTrue($this->service->shouldSkipTime($id, $this->game['id'])); // Time is already skipped so in reality, it will not skip the time

        Redis::set("game:{$this->game['id']}:votes", []);

        $id = $this->service->create($this->game['id'], Interaction::Mayor)['id'];
        $this->service->call(InteractionAction::Elect, $id, $this->psychic->id, $this->werewolf->id);
        $this->assertFalse($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionAction::Elect, $id, $this->user->id, $this->werewolf->id);
        $this->assertFalse($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionAction::Elect, $id, $this->infectedWerewolf->id, $this->werewolf->id);
        $this->assertTrue($this->service->shouldSkipTime($id, $this->game['id']));
    }

    public function testCreatingAngelInteraction()
    {
        $gameId = $this->game['id'];
        $interaction = $this->service->create($gameId, Interaction::Angel, $this->getUserIdByRole(Role::Angel, $gameId));

        $this->assertSame([
            'gameId' => $gameId,
            'id' => $interaction['id'],
            'authorizedCallers' => json_encode([$this->angel->id]),
            'type' => Interaction::Angel->value,
            'data' => $interaction['data'],
        ], $interaction);

        $this->assertNotSame($this->angel->id, $interaction['data']);
        $this->assertContains($interaction['data'], $this->game['users']);
        $this->assertSame(Redis::get("game:$gameId")['angel_target'], $interaction['data']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->witch, $this->psychic, $this->werewolf, $this->infectedWerewolf, $this->angel, $this->guard] = User::factory(7)->create();
        $users = [$this->user, $this->witch, $this->psychic, $this->werewolf, $this->infectedWerewolf, $this->angel, $this->guard];
        $this->service = new InteractionService(new VoteService());

        $this->game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'roles' => [1, 2, 3, 4, 7],
            ])
            ->json('data.game');

        $additionnalKeys = array_merge($this->game, [
            'assigned_roles' => [
                $this->werewolf->id => 1,
                $this->user->id => 2,
                $this->psychic->id => 3,
                $this->witch->id => 4,
                $this->infectedWerewolf->id => Role::InfectedWerewolf,
                $this->angel->id => Role::Angel,
                $this->guard->id => Role::Guard,
            ],
            'users' => array_map(fn ($user) => $user->id, $users),
            'is_started' => true,
            'werewolves' => [
                $this->werewolf->id, $this->infectedWerewolf->id,
            ],
        ]);

        $members = [];

        foreach ($users as $user) {
            $user->current_game = $this->game['id'];
            $user->save();
            $members[] = ['user_id' => $user->id, 'user_info' => $user];
        }

        Redis::set("game:{$this->game['id']}:members", $members);
        Redis::set("game:{$this->game['id']}:state", [
            'status' => State::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => State::Vote->duration(),
        ]);
        Redis::set("game:{$this->game['id']}", $additionnalKeys);

        $this->game = $additionnalKeys;
    }
}
