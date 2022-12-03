<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\States;
use App\Enums\Teams;
use App\Events\ChatLock;
use App\Events\MessageSended;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToDockerNetwork;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GameChatControllerTest extends TestCase
{
    public function testSendingMessage()
    {
        Event::fake();

        $this
            ->actingAs($this->user, 'api')
            ->post('/api/game/message/send', [
                'content' => 'A beautiful message',
                'gameId' => $this->game['id'],
            ])
            ->assertNoContent();

        Event::assertDispatched(MessageSended::class);
    }

    public function testMessageAuthorIsRestrictedInformations()
    {
        Event::fake();

        $user = $this->user;
        $this->actingAs($user, 'api')->post('/api/game/message/send', [
            'content' => 'A beautiful message',
            'gameId' => $this->game['id'],
        ]);
        Event::assertDispatched(function (MessageSended $event) use ($user) {
            $author = ((array) $event)['payload']['author'];

            return $author === [
                'id' => $user->id,
                'username' => $user->username,
                'avatar' => $user->avatar,
            ];
        });
    }

    public function testSendingMessageToUnexistingGame()
    {
        Event::fake();
        $this->actingAs($this->user, 'api')->post('/api/game/message/send', [
            'content' => 'A beautiful message',
            'gameId' => 'aeazrazerazerazeraze',
        ])->assertJsonValidationErrorFor('gameId');
        Event::assertNotDispatched(MessageSended::class);
    }

    public function testSendingWhileNotBeingInTheGame()
    {
        Event::fake();
        $this->actingAs($this->secondUser, 'api')->post('/api/game/message/send', [
            'content' => 'A beautiful message',
            'gameId' => $this->game['id'],
        ])->assertJsonValidationErrorFor('gameId');
        Event::assertNotDispatched(MessageSended::class);
    }

    public function testSendingWerewolfMessage()
    {
        Event::fake();

        $gameId = $this->game['id'];
        $user = $this->user;
        $state = Redis::get("game:{$gameId}:state");
        $state['status'] = States::Werewolf->value;
        Redis::set("game:$gameId:state", $state);

        $this
            ->actingAs($this->user, 'api')
            ->post('/api/game/message/send', [
                'content' => "I'm a werewolf :)",
                'gameId' => $this->game['id'],
            ])
            ->assertNoContent();

        Event::assertDispatched(function (MessageSended $event) use ($user, $gameId) {
            return (array) $event === [
                'payload' => [
                    'gameId' => $gameId,
                    'content' => "I'm a werewolf :)",
                    'author' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'avatar' => $user->avatar,
                    ],
                    'type' => 'werewolf',
                ],
                'private' => true,
                'emitters' => [$user->id],
                'socket' => null,
            ];
        });
    }

    public function testLockingChat()
    {
        Event::fake();

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/chat/lock', ['gameId' => $this->game['id']])
            ->assertNoContent();

        Event::assertDispatched(ChatLock::class);
    }

    public function testPrivateChatLocking()
    {
        Event::fake();

        $user = $this->user;
        $game = $this->game;

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/chat/lock', [
                'gameId' => $game['id'],
                'users' => $this->user->id,
            ])
            ->assertNoContent();

        Event::assertDispatched(function (ChatLock $event) use ($game, $user) {
            return (array) $event === [
                'gameId' => $game['id'],
                'private' => true,
                'emitters' => [$user->id],
                'socket' => null,
            ];
        });
    }

    public function testLockingChatForATeam()
    {
        Event::fake();

        $game = $this->game;
        $user = $this->user;

        $this
            ->withoutMiddleware(RestrictToDockerNetwork::class)
            ->post('/api/game/chat/lock', [
                'gameId' => $game['id'],
                'team' => Teams::Werewolves->value,
            ])
            ->assertNoContent();

        Event::assertDispatched(function (ChatLock $event) use ($game, $user) {
            return (array) $event === [
                'gameId' => $game['id'],
                'private' => true,
                'emitters' => [$user->id],
                'socket' => null,
            ];
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secondUser = User::factory()->create();

        $this->game = $this->actingAs($this->user, 'api')->put('/api/game', [
            'roles' => [1, 2],
            'users' => [],
        ])['game'];

        $game = Redis::get("game:{$this->game['id']}");

        $game['assigned_roles'] = [
            $this->user->id => 1,
            $this->secondUser->id => 2,
        ];

        $game['is_started'] = true;

        Redis::set("game:{$this->game['id']}", $game);
    }
}
