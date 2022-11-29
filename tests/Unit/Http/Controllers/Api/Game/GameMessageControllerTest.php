<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Events\MessageSended;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GameMessageControllerTest extends TestCase
{
    public function testSendingMessage()
    {
        Event::fake();
        $this->actingAs($this->user, 'api')->post('/api/game/message/send', [
            'content' => 'A beautiful message',
            'gameId' => $this->game['id'],
        ]);
        Event::assertDispatched(MessageSended::class);
    }

    public function testMessageAuthorIsRestrictedInformations()
    {
        $user = $this->user;
        Event::fake();
        $this->actingAs($user, 'api')->post('/api/game/message/send', [
            'content' => 'A beautiful message',
            'gameId' => $this->game['id'],
        ]);
        Event::assertDispatched(function (MessageSended $event) use ($user) {
            $author = ((array) $event)['message']['author'];

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->secondUser = User::factory()->create();

        $this->game = $this->actingAs($this->user, 'api')->put('/api/game', [
            'roles' => [1, 2],
            'users' => [],
        ])['game'];
    }
}
