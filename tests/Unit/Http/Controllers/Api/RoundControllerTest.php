<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Enums\Rounds;
use Tests\TestCase;

class RoundControllerTest extends TestCase
{
    public function testGettingAllRounds()
    {
        $rounds = Rounds::cases();
        $rounds = array_map(fn ($round) => $round->stateify(), $rounds);

        $this
            ->get('/api/rounds')
            ->assertOk()
            ->assertExactJson($rounds);
    }

    public function testGettingOneRound()
    {
        $this
            ->get('/api/round/1')
            ->assertOk()
            ->assertExactJson([Rounds::FirstRound]);
    }
}
