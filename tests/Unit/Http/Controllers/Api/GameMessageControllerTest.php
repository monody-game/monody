<?php

namespace Tests\Unit\Http\Controllers\Api;

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
            'gameId' => $this->game['id']
        ]);
        Event::assertDispatched(MessageSended::class);
    }

    public function testSendingMessageToUnexistingGame()
    {
        Event::fake();
        $this->actingAs($this->user, 'api')->post('/api/game/message/send', [
            'content' => 'A beautiful message',
            'gameId' => 'aeazrazerazerazeraze'
        ])->assertStatus(404);
        Event::assertNotDispatched(MessageSended::class);
    }

    public function testSendingWhileNotBeingInTheGame()
    {
        Event::fake();
        $this->actingAs($this->secondUser, 'api')->post('/api/game/message/send', [
            'content' => 'A beautiful message',
            'gameId' => $this->game['id']
        ])->assertStatus(401);
        Event::assertNotDispatched(MessageSended::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(1);
        $this->secondUser = User::find(2);

        $this->game = $this->actingAs($this->user, 'api')->post('/api/game/new', [
            'roles' => [1, 2],
            'users' => []
        ])['game'];
    }
}
