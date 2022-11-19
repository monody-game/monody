<?php

namespace Http\Controllers\Api;

use App\Enums\States;
use Tests\TestCase;

class StateControllerTest extends TestCase
{
    public function testGettingDetailsOnAState()
    {
        $state = States::from(1);
        $this
            ->get('/api/state/1')
            ->assertOk()
            ->assertExactJson([
                'state' => $state->value,
                'icon' => $state->iconify(),
                'raw_name' => $state->stringify(),
                'name' => $state->readeableStringify(),
                'duration' => $state->duration(),
            ]);
    }
}
