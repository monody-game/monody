<?php

namespace Tests\Unit\Services;

use App\Enums\GameInteractions;
use App\Enums\InteractionActions;
use App\Enums\Roles;
use App\Events\InteractionClose;
use App\Events\InteractionCreate;
use App\Facades\Redis;
use App\Models\User;
use App\Services\InteractionService;
use App\Traits\MemberHelperTrait;
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

    public function testCreatingAnInteraction()
    {
        Event::fake();

        $expectedInteraction = [
            'gameId' => 'test',
            'authorizedCallers' => '*',
            'type' => 'vote',
        ];

        $interaction = $this->service->create('test', GameInteractions::Vote);

        Event::assertDispatched(function (InteractionCreate $event) use ($interaction) {
            $event = (array) $event;

            return $event['payload'] === [
                'gameId' => 'test',
                'interactionId' => $interaction['interactionId'],
                'authorizedCallers' => '*',
                'type' => GameInteractions::Vote->value,
            ];
        });

        $redisInteraction = Redis::get('game:test:interactions');
        $expectedInteraction['interactionId'] = $interaction['interactionId'];

        $this->assertSame(sort($expectedInteraction), sort($interaction));
        $this->assertSame(sort($expectedInteraction), sort($redisInteraction[0]));
    }

    public function testEndingInteraction()
    {
        Event::fake();

        $interaction = $this->service->create('otherGame', GameInteractions::Vote);
        $this->service->close('otherGame', $interaction['interactionId']);

        $this->assertEmpty(Redis::get('game:otherGame:interactions'));

        Event::assertDispatched(function (InteractionClose $event) use ($interaction) {
            return $event->payload === [
                'gameId' => 'otherGame',
                'interactionId' => $interaction['interactionId'],
            ];
        });
    }

    public function testUsingInteraction()
    {
        // Psychic
        $spectate = $this->service->call(InteractionActions::Spectate, $this->psychic->id, $this->user->id);
        $this->assertSame(Roles::SimpleVillager->value, $spectate);

        // Witch
        $witchNone = $this->service->call(InteractionActions::WitchSkip, $this->witch->id, $this->witch->id);
        $this->assertNull($witchNone);
        $this->service->call(InteractionActions::KillPotion, $this->witch->id, $this->user->id);
        $this->assertFalse($this->alive($this->user->id, $this->game['id']));
        $this->service->call(InteractionActions::RevivePotion, $this->witch->id, $this->user->id);
        $this->assertTrue($this->alive($this->user->id, $this->game['id']));
    }

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->witch, $this->psychic, $this->werewolf] = User::factory(4)->create();
        $users = [$this->user, $this->witch, $this->psychic, $this->werewolf];
        $this->service = new InteractionService();

        $this->game = $this
            ->actingAs($this->user, 'api')
            ->post('/api/game/new', [
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
