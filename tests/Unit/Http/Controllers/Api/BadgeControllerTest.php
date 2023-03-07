<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Badges;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Support\Carbon;
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

        $service = new BadgeService();
        $service->add($user, Badges::Wins);
        $service->add($user, Badges::Level, 4);

        $expected = [...$this->badges];
        $expected[2] = [
            'id' => Badges::Wins->value,
            'display_name' => Badges::Wins->stringify(),
            'description' => Badges::Wins->describe(),
            'owned' => true,
            'max_level' => Badges::Wins->maxLevel(),
            'current_level' => 1,
            'obtained_at' => Carbon::now()->toDateString() . ' ' . Carbon::now()->toTimeString(),
        ];

        $expected[4] = [
            'id' => Badges::Level->value,
            'display_name' => Badges::Level->stringify(),
            'description' => Badges::Level->describe(),
            'owned' => true,
            'max_level' => Badges::Level->maxLevel(),
            'current_level' => 4,
            'obtained_at' => Carbon::now()->toDateString() . ' ' . Carbon::now()->toTimeString(),
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
            if ($badge->value === 0) {
                continue;
            }

            $this->badges[] = [
                'id' => $badge->value,
                'display_name' => $badge->stringify(),
                'description' => $badge->describe(),
                'owned' => false,
                'max_level' => $badge->maxLevel(),
                'current_level' => 0,
                'obtained_at' => null,
            ];
        }
    }
}
