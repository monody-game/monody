<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\Roles;
use App\Enums\Teams;
use App\Events\GameEnd;
use App\Events\GameLoose;
use App\Events\GameWin;
use App\Facades\Redis;
use App\Http\Middleware\RestrictToLocalNetwork;
use App\Models\User;
use App\Notifications\ExpEarned;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EndGameControllerTest extends TestCase
{
    private User $user;

    private User $secondUser;

    private array $game;

    public function testCheckingIfAGameCanEnd()
    {
        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/end/check', [
                'gameId' => $this->game['id'],
            ])
            ->assertForbidden();

        Redis::set(
            "game:{$this->game['id']}",
            array_merge(Redis::get("game:{$this->game['id']}"), ['dead_users' => [$this->user->id]])
        );

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/end/check', [
                'gameId' => $this->game['id'],
            ])
            ->assertNoContent();
    }

    public function testEndingAGame()
    {
        Event::fake();
        Notification::fake();

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => array_merge($this->user->toArray(), ['is_dead' => true])],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);

        $villager = $this->user;
        $werewolf = $this->secondUser;
        $gameId = $this->game['id'];

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/end', [
                'gameId' => $gameId,
            ])
            ->assertNoContent();

        $game = Redis::get("game:$gameId");
        $this->assertTrue($game['ended']);

        Notification::assertSentTo($villager, ExpEarned::class, function ($notification) use ($villager, $werewolf) {
            $user = $villager->refresh();
            $werewolf = $werewolf->refresh();

            return $notification->exp->toArray()['exp'] === 10 && $user->level === 2 && $werewolf->level = 3;
        });

        Notification::assertSentTo($villager, ExpEarned::class, function ($notification) use ($villager, $werewolf) {
            $user = $villager->refresh();
            $werewolf = $werewolf->refresh();

            // Create a game that is ended
            return $notification->exp->toArray()['exp'] === (10 + 20) && $user->level === 2 && $werewolf->level = 3;
        });

        Notification::assertSentTo($werewolf, ExpEarned::class, function ($notification) use ($werewolf) {
            $werewolf = $werewolf->refresh();

            return $notification->exp->toArray()['exp'] === 50 && $werewolf->level === 3;
        });

        Event::assertDispatched(function (GameEnd $event) use ($werewolf, $gameId) {
            return ((array) $event)['payload'] === [
                'gameId' => $gameId,
                'winners' => [
                    $werewolf->id => Roles::Werewolf,
                ],
                'winningTeam' => Teams::Werewolves,
            ];
        });

        Event::assertDispatched(function (GameLoose $event) use ($villager, $gameId) {
            return (array) $event === [
                'payload' => [
                    'gameId' => $gameId,
                ],
                'private' => true,
                'emitters' => [$villager->id],
                'socket' => null,
            ];
        });

        Event::assertDispatched(function (GameWin $event) use ($werewolf, $gameId) {
            return (array) $event === [
                'payload' => [
                    'gameId' => $gameId,
                ],
                'private' => true,
                'emitters' => [$werewolf->id],
                'socket' => null,
            ];
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        [$this->user, $this->secondUser] = User::factory(2)->create([
            'level' => 3,
        ]);

        $this->user->level = 1;
        $this->user->save();

        $this->game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'roles' => [1, 2],
                'users' => [$this->secondUser->id],
            ])
            ->json('game');

        Redis::set("game:{$this->game['id']}:members", [
            ['user_id' => $this->user['id'], 'user_info' => $this->user],
            ['user_id' => $this->secondUser['id'], 'user_info' => $this->secondUser],
        ]);

        $additionnalKeys = [
            'assigned_roles' => [
                $this->secondUser->id => 1,
                $this->user->id => 2,
            ],
            'is_started' => true,
            'werewolves' => [
                $this->secondUser->id,
            ],
        ];

        Redis::set("game:{$this->game['id']}", array_merge(Redis::get("game:{$this->game['id']}"), $additionnalKeys));
    }
}
