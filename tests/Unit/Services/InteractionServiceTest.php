<?php

namespace Tests\Unit\Services;

use App\Enums\InteractionActions;
use App\Enums\Interactions;
use App\Enums\Roles;
use App\Enums\States;
use App\Events\MayorElected;
use App\Models\User;
use App\Services\InteractionService;
use App\Services\VoteService;
use App\Traits\InteractsWithRedis;
use App\Traits\MemberHelperTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InteractionServiceTest extends TestCase
{
    use MemberHelperTrait, InteractsWithRedis;

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

        $interaction = $this->service->create('test', Interactions::Vote);

        $redisInteraction = $this->redis()->get('game:test:interactions');
        $expectedInteraction['id'] = $interaction['id'];

        $this->assertSame(sort($expectedInteraction), sort($interaction));
        $this->assertSame(sort($expectedInteraction), sort($redisInteraction[0]));
    }

    public function testEndingInteraction()
    {
        $interaction = $this->service->create($this->game['id'], Interactions::Vote);
        $this->assertNull($this->service->close($this->game['id'], $interaction['id']));

        $this->assertEmpty($this->redis()->get('game:otherGame:interactions'));
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

        $interaction = $this->service->create($this->game['id'], Interactions::Witch, $this->witch->id, [$this->user->id]);
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
        $this->service->call(InteractionActions::RevivePotion, $id, $this->witch->id, $this->user->id);
        $this->assertTrue($this->alive($this->user->id, $this->game['id']));

        // Werewolves
        $id = $this->service->create($this->game['id'], Interactions::Werewolves)['id'];
        $this->service->call(InteractionActions::Kill, $id, $this->werewolf->id, $this->psychic->id);
        $this->service->close($this->game['id'], $id);
        $this->assertFalse($this->alive($this->psychic->id, $this->game['id']));

        // Infected Werewolf
        $id = $this->service->create($this->game['id'], Interactions::InfectedWerewolf)['id'];
        $this->service->call(InteractionActions::InfectedSkip, $id, $this->infectedWerewolf->id, $this->psychic->id);
        $this->service->close($this->game['id'], $id);
        $this->assertFalse($this->alive($this->psychic->id, $this->game['id']));

        $id = $this->service->create($this->game['id'], Interactions::InfectedWerewolf)['id'];
        $this->service->call(InteractionActions::Infect, $id, $this->infectedWerewolf->id, $this->psychic->id);
        $this->service->close($this->game['id'], $id);
        $this->assertTrue($this->alive($this->psychic->id, $this->game['id']));
        $this->assertContains($this->psychic->id, $this->redis()->get("game:{$this->game['id']}")['werewolves']);
        $this->assertEmpty($this->redis()->get("game:{$this->game['id']}:deaths"));

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

    public function testElectingAMayor()
    {
        Event::fake();

        $userId = $this->werewolf->id;
        $gameId = $this->game['id'];

        $id = $this->service->create($gameId, Interactions::Mayor)['id'];
        $this->service->call(InteractionActions::Elect, $id, $this->psychic->id, $userId);
        $this->service->call(InteractionActions::Elect, $id, $userId, $userId);
        $this->service->call(InteractionActions::Elect, $id, $this->witch->id, $userId);
        $this->service->close($gameId, $id);

        Event::assertDispatched(function (MayorElected $event) use ($userId, $gameId) {
            return ((array) $event)['payload'] === [
                'gameId' => $gameId,
                'mayor' => $userId,
            ];
        });

        $game = $this->redis()->get("game:{$this->game['id']}");

        $this->assertArrayHasKey('mayor', $game);
        $this->assertSame($userId, $game['mayor']);
    }

    public function testShouldSkipTimer()
    {
        $id = $this->service->create($this->game['id'], Interactions::Psychic)['id'];
        $this->service->call(InteractionActions::Spectate, $id, $this->psychic->id, $this->werewolf->id);
        $this->assertTrue($this->service->shouldSkipTime($id, $this->game['id']));

        $id = $this->service->create($this->game['id'], Interactions::Vote)['id'];
        $this->service->call(InteractionActions::Vote, $id, $this->psychic->id, $this->werewolf->id);
        $this->assertFalse($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionActions::Vote, $id, $this->user->id, $this->werewolf->id);
        $this->assertFalse($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionActions::Vote, $id, $this->infectedWerewolf->id, $this->werewolf->id);
        $this->assertTrue($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionActions::Vote, $id, $this->witch->id, $this->werewolf->id);
        $this->assertTrue($this->service->shouldSkipTime($id, $this->game['id'])); // Time is already skipped so in reality, it will not skip the time

        $this->redis()->set("game:{$this->game['id']}:votes", []);

        $id = $this->service->create($this->game['id'], Interactions::Mayor)['id'];
        $this->service->call(InteractionActions::Elect, $id, $this->psychic->id, $this->werewolf->id);
        $this->assertFalse($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionActions::Elect, $id, $this->user->id, $this->werewolf->id);
        $this->assertFalse($this->service->shouldSkipTime($id, $this->game['id']));

        $this->service->call(InteractionActions::Elect, $id, $this->infectedWerewolf->id, $this->werewolf->id);
        $this->assertTrue($this->service->shouldSkipTime($id, $this->game['id']));
    }

    public function testCreatingAngelInteraction()
    {
        $gameId = $this->game['id'];
        $interaction = $this->service->create($gameId, Interactions::Angel, $this->getUserIdByRole(Roles::Angel, $gameId));

        $this->assertSame([
            'gameId' => $gameId,
            'id' => $interaction['id'],
            'authorizedCallers' => json_encode([$this->angel->id]),
            'type' => Interactions::Angel->value,
            'data' => $interaction['data'],
        ], $interaction);

        $this->assertNotSame($this->angel->id, $interaction['data']);
        $this->assertContains($interaction['data'], $this->game['users']);
        $this->assertSame($this->redis()->get("game:$gameId")['angel_target'], $interaction['data']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->witch, $this->psychic, $this->werewolf, $this->infectedWerewolf, $this->angel] = User::factory(6)->create();
        $users = [$this->user, $this->witch, $this->psychic, $this->werewolf, $this->infectedWerewolf, $this->angel];
        $this->service = new InteractionService(new VoteService());

        $this->game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'roles' => [1, 2, 3, 4, 7],
            ])
            ->json('game');

        $additionnalKeys = array_merge($this->game, [
            'assigned_roles' => [
                $this->werewolf->id => 1,
                $this->user->id => 2,
                $this->psychic->id => 3,
                $this->witch->id => 4,
                $this->infectedWerewolf->id => Roles::InfectedWerewolf,
                $this->angel->id => Roles::Angel,
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

        $this->redis()->set("game:{$this->game['id']}:members", $members);
        $this->redis()->set("game:{$this->game['id']}:state", [
            'status' => States::Vote->value,
            'startTimestamp' => Date::now()->subSeconds(50)->timestamp,
            'counterDuration' => States::Vote->duration(),
        ]);
        $this->redis()->set("game:{$this->game['id']}", $additionnalKeys);

        $this->game = $additionnalKeys;
    }
}
