<?php

namespace Tests\Unit\Services;

use App\Enums\Badges;
use App\Models\Badge;
use App\Models\Exp;
use App\Models\GameOutcome;
use App\Models\User;
use App\Notifications\BadgeGranted;
use App\Services\BadgeService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
{
    private BadgeService $service;

    public function testGettingUserBadges()
    {
        $user = User::factory()->create();

        $this->assertSame([], $this->service->get($user));

        $badge = new Badge([
            'user_id' => $user->id,
            'badge_id' => Badges::Owner->value,
            'level' => 0,
            'obtained_at' => '1970-01-01 00:00:00',
        ]);
        $badge->save();

        $badge = new Badge([
            'user_id' => $user->id,
            'badge_id' => Badges::Wins->value,
            'level' => 4,
            'obtained_at' => '1970-01-01 00:00:00',
        ]);
        $badge->save();

        $this->assertSame([
            [
                'badge' => Badges::Owner,
                'user_id' => $user->id,
                'level' => 0,
                'obtained_at' => '1970-01-01 00:00:00',
            ],
            [
                'badge' => Badges::Wins,
                'user_id' => $user->id,
                'level' => 4,
                'obtained_at' => '1970-01-01 00:00:00',
            ],
        ], $this->service->get($user));
    }

    public function testAddingBadge()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->assertSame([], $this->service->get($user));

        $this->service->add($user, Badges::Owner);

        Notification::assertSentTo($user, BadgeGranted::class, function ($notification) {
            return $notification->payload['badge'] === Badges::Owner;
        });

        $this->assertSame([
            [
                'badge' => Badges::Owner,
                'user_id' => $user->id,
                'level' => -1,
                'obtained_at' => Carbon::now()->toDateString() . ' ' . Carbon::now()->toTimeString(),
            ],
        ], $this->service->get($user));
    }

    public function testAddingMaxLevelBadge()
    {
        $user = User::factory()->create();
        $badges = collect();

        for ($i = 1; $i < Badges::Wins->maxLevel(); $i++) {
            $badges[] = $badge = new Badge([
                'user_id' => $user->id,
                'badge_id' => Badges::Wins->value,
                'level' => $i,
                'obtained_at' => '1970-01-01 00:00:00',
            ]);
            $badge->save();
        }

        $badges = $badges->map(function (Badge $badge) {
            return [
                'badge' => Badges::from($badge->badge_id),
                'user_id' => $badge->user_id,
                'level' => $badge->level,
                'obtained_at' => $badge->obtained_at,
            ];
        });

        $this->service->add($user, Badges::Wins);

        $badges[] = [
            'badge' => Badges::Wins,
            'user_id' => $user->id,
            'level' => 5,
            'obtained_at' => Carbon::now()->toDateString() . ' ' . Carbon::now()->toTimeString(),
        ];

        $this->assertSame($badges->toArray(), $this->service->get($user));
    }

    public function testDetectingIfUserCanHaveABadge()
    {
        $user = User::factory()->create();

        GameOutcome::factory(15)->create([
            'user_id' => $user->id,
            'win' => true,
        ]);

        $this->assertTrue(BadgeService::canAccess($user, Badges::Wins));
        $this->assertFalse(BadgeService::canAccess($user, Badges::Losses));

        $this->service->add($user, Badges::Wins);

        $this->assertFalse(BadgeService::canAccess($user, Badges::Wins));
    }

    public function testGrantingBadgeMakeUserGainExp()
    {
        $user = User::factory([
            'level' => 10,
        ])->create();

        $this->assertNull(Exp::where('user_id', $user->id)->first());

        $this->service->add($user, Badges::Wins, 3);

        $this->assertSame(Badges::Wins->gainedExp(3), Exp::where('user_id', $user->id)->first()->exp);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app()->make(BadgeService::class);
    }
}
