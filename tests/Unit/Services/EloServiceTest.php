<?php

namespace Services;

use App\Facades\Redis;
use App\Models\Elo;
use App\Models\User;
use App\Services\EloService;
use Tests\TestCase;

class EloServiceTest extends TestCase
{
    public function testAddingElo()
    {
        $service = new EloService();
        $user = User::factory()->createOne();
        $elo = Elo::factory()->createOne(['user_id' => $user->id]);

        $service->add(50, $user->id);

        $this->assertSame(2050, $elo->fresh()->elo);

        $service->add(-150, $user->id);

        $this->assertSame(1900, $elo->fresh()->elo);
    }

    public function testManagingEloAutomatically()
    {
        $service = new EloService();
        $users = User::factory(3)->create();
        Elo::factory()->createMany([
            ['user_id' => $users[0]->id, 'elo' => 2250],
            ['user_id' => $users[1]->id, 'elo' => 2500],
            ['user_id' => $users[2]->id, 'elo' => 2750],
        ]);

        Redis::set('game:testEloGame', [
            'users' => [...$users->map(fn ($user) => $user->id)],
        ]);

        $this->assertThat(
            $service->computeElo($users[0]->id, 'testEloGame', true),
            $this->logicalAnd(
                $this->greaterThanOrEqual(40),
                $this->lessThanOrEqual(50)
            )
        );

        $this->assertThat(
            $service->computeElo($users[1]->id, 'testEloGame'),
            $this->logicalAnd(
                $this->greaterThanOrEqual(20),
                $this->lessThanOrEqual(40)
            )
        );

        $this->assertThat(
            $service->computeElo($users[2]->id, 'testEloGame', false),
            $this->logicalAnd(
                $this->greaterThanOrEqual(-66),
                $this->lessThanOrEqual(-33)
            )
        );
    }
}
