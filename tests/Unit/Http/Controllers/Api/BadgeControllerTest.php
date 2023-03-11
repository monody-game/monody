<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Badges;
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
            ->assertJson($this->badges);
    }

    public function testRetrievingUserBadges()
    {
        $user = User::factory()->create();

        $service = app()->make(BadgeService::class);
        $service->add($user, Badges::Wins);
        $service->add($user, Badges::Level, 4);

        $expected = [...$this->badges];
        $expected[0] = [
            'id' => Badges::Wins->value,
            'name' => Badges::Wins->name(),
            'display_name' => Badges::Wins->stringify(),
            'explanation' => Badges::Wins->describe(),
            'description' => Badges::Wins->description(),
            'owned' => true,
            'max_level' => Badges::Wins->maxLevel(),
            'current_level' => 1,
            'secret' => false,
        ];

        $expected[2] = [
            'id' => Badges::Level->value,
            'name' => Badges::Level->name(),
            'display_name' => Badges::Level->stringify(),
            'explanation' => Badges::Level->describe(),
            'description' => Badges::Level->description(),
            'owned' => true,
            'max_level' => Badges::Level->maxLevel(),
            'current_level' => 4,
            'secret' => false,
        ];

        $this
            ->actingAs($user)
            ->getJson('/api/badges')
            ->assertJson($expected);

        $this
            ->getJson("/api/badges/{$user->id}")
            ->assertJson($expected);
    }

    public function setUp(): void
    {
        parent::setUp();

        foreach (Badges::cases() as $badge) {
            $this->badges[] = $badge->full();
        }
    }
}
