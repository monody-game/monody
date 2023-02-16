<?php

namespace Tests\Unit\Services;

use App\Models\Exp;
use App\Models\User;
use App\Notifications\ExpEarned;
use App\Notifications\LevelUp;
use App\Services\ExpService;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExpServiceTest extends TestCase
{
    private ExpService $service;

    public function testAddingExp(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->assertNull(Exp::where('user_id', $user->id)->first());

        $this->service->add(4, $user);

        Notification::assertSentTo([$user], ExpEarned::class);
        $this->assertSame(4, Exp::where('user_id', $user->id)->first()->exp);
    }

    public function testRetrievingExpNeededForNextLevel(): void
    {
        $this->assertSame(10, $this->service->nextLevelExp(1));
        $this->assertSame(32, $this->service->nextLevelExp(2));
        $this->assertSame(64, $this->service->nextLevelExp(3));
        $this->assertSame(105, $this->service->nextLevelExp(4));
    }

    public function testLevelingUp(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->assertSame(1, $user->level);

        $this->service->add(10, $user);
        Notification::assertSentTo([$user], ExpEarned::class);
        Notification::assertSentTo([$user], LevelUp::class);

        $user = $user->refresh();

        $this->assertSame(0, Exp::where('user_id', $user->id)->first()->exp);
        $this->assertSame(2, $user->level);
    }

    public function testAddingMoreExpThanNeededToLevelUp(): void
    {
        Notification::fake();

        $user = User::factory([
            'level' => 2,
        ])->create();

        $this->service->add(50, $user);
        Notification::assertSentTo([$user], ExpEarned::class);
        Notification::assertSentTo([$user], LevelUp::class);

        $user = $user->refresh();

        $this->assertSame(18, Exp::where('user_id', $user->id)->first()->exp);
        $this->assertSame(3, $user->level);
    }

    public function setUp(): void
    {
        $this->service = new ExpService();

        parent::setUp();
    }
}
