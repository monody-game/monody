<?php

namespace Tests\Unit\Http\Controllers\Api\Game;

use App\Enums\Role;
use App\Enums\Team;
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
            array_merge(Redis::get("game:{$this->game['id']}"), ['dead_users' => [$this->user->id => []]])
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

        Redis::set("game:{$this->game['id']}", array_merge(
            Redis::get("game:{$this->game['id']}"),
            ['dead_users' => [$this->user->id => ['round' => 1, 'context' => 'werewolves']]]
        ));

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
                    $werewolf->id => Role::Werewolf->full(),
                ],
                'winningTeam' => Team::Werewolves,
            ];
        });

        Event::assertDispatched(function (GameLoose $event) use ($villager, $gameId) {
            return (array) $event === [
                'payload' => [
                    'gameId' => $gameId,
                ],
                'private' => true,
                'recipients' => [$villager->id],
                'socket' => null,
            ];
        });

        Event::assertDispatched(function (GameWin $event) use ($werewolf, $gameId) {
            return (array) $event === [
                'payload' => [
                    'gameId' => $gameId,
                ],
                'private' => true,
                'recipients' => [$werewolf->id],
                'socket' => null,
            ];
        });
    }

    public function testCheckingIfTheGameShouldEndWithTheWhiteWerewolf()
    {
        $third = User::factory()->create();
        Event::fake();

        $game = $this
            ->actingAs($this->user, 'api')
            ->put('/api/game', [
                'roles' => [Role::WhiteWerewolf->value, Role::Werewolf->value, Role::SimpleVillager->value],
                'users' => [$this->secondUser->id, $third->id, $this->user->id],
            ])
            ->json('data.game');

        $gameId = $game['id'];

        $additionnalKeys = [
            'assigned_roles' => [
                $this->secondUser->id => Role::WhiteWerewolf->value,
                $this->user->id => Role::Werewolf->value,
                $third->id => Role::SimpleVillager->value,
            ],
            'is_started' => true,
            'werewolves' => [
                $this->user->id,
                $this->secondUser->id,
            ],
        ];

        Redis::set("game:$gameId", array_merge(Redis::get("game:$gameId"), $additionnalKeys));

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/end/check', [
                'gameId' => $gameId,
            ])
            ->assertForbidden();

        Redis::set("game:$gameId", array_merge(Redis::get("game:$gameId"), [
            'dead_users' => [
                $this->user->id => ['round' => 1, 'context' => 'white_werewolf'],
                $third->id => ['round' => 1, 'context' => 'null'],
            ],
        ]));

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/end/check', [
                'gameId' => $gameId,
            ])
            ->assertNoContent();

        $this
            ->withoutMiddleware(RestrictToLocalNetwork::class)
            ->post('/api/game/end', [
                'gameId' => $gameId,
            ])
            ->assertNoContent();

        $game = Redis::get("game:$gameId");
        $this->assertTrue($game['ended']);

        Event::assertDispatched(function (GameEnd $event) use ($gameId) {
            return ((array) $event)['payload'] === [
                'gameId' => $gameId,
                'winners' => [
                    $this->secondUser->id => Role::WhiteWerewolf->full(),
                ],
                'winningTeam' => Team::Loners,
            ];
        });

        Event::assertDispatched(function (GameWin $event) use ($gameId) {
            return (array) $event === [
                'payload' => [
                    'gameId' => $gameId,
                ],
                'private' => true,
                'recipients' => [$this->secondUser->id],
                'socket' => null,
            ];
        });

        Event::assertDispatched(function (GameLoose $event) use ($gameId, $third) {
            return (array) $event === [
                'payload' => [
                    'gameId' => $gameId,
                ],
                'private' => true,
                'recipients' => [$third->id, $this->user->id],
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
                'roles' => [1, 2, 2, 2, 2],
                'users' => [$this->secondUser->id],
            ])
            ->json('data.game');

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
