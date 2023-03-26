<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Badge;
use App\Models\User;
use App\Services\BadgeService;
use Tests\TestCase;

class BadgeControllerTest extends TestCase
{
    private array $badges = [];

    public function testRetrievingBadges()
    {
        $this
            ->getJson('/api/badges')
            ->assertJsonPath('data.badges', $this->badges);
    }

    public function testRetrievingUserBadges()
    {
        $user = User::factory()->create();

        $service = app()->make(BadgeService::class);
        $service->add($user, Badge::Wins);
        $service->add($user, Badge::Level, 4);

        $expected = [...$this->badges];
        $expected[0] = [
            'id' => Badge::Wins->value,
            'name' => Badge::Wins->name(),
            'display_name' => Badge::Wins->stringify(),
            'explanation' => Badge::Wins->describe(),
            'description' => Badge::Wins->description(),
            'owned' => true,
            'max_level' => Badge::Wins->maxLevel(),
            'current_level' => 1,
            'secret' => false,
        ];

        $expected[2] = [
            'id' => Badge::Level->value,
            'name' => Badge::Level->name(),
            'display_name' => Badge::Level->stringify(),
            'explanation' => Badge::Level->describe(),
            'description' => Badge::Level->description(),
            'owned' => true,
            'max_level' => Badge::Level->maxLevel(),
            'current_level' => 4,
            'secret' => false,
        ];

        $this
            ->actingAs($user)
            ->getJson('/api/badges')
            ->assertJson(['data' => ['badges' => $expected]]);

        $this
            ->getJson("/api/badges/{$user->id}")
            ->assertJson(['data' => ['badges' => $expected]]);
    }

    public function setUp(): void
    {
        parent::setUp();

        foreach (Badge::cases() as $badge) {
            $this->badges[] = $badge->full();
        }
    }
}
