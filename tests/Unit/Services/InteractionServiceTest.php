<?php

namespace Tests\Unit\Services;

use App\Enums\GameInteractions;
use App\Events\InteractionClose;
use App\Events\InteractionCreate;
use App\Facades\Redis;
use App\Services\InteractionService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InteractionServiceTest extends TestCase
{
    private InteractionService $service;

    public function testCreatingAnInteraction()
    {
        Event::fake();

        $expectedInteraction = [
            'gameId' => 'test',
            'authorizedCallers' => '*',
            'type' => 'vote',
        ];

        $interaction = $this->service->create('test', GameInteractions::Vote);

        Event::assertDispatched(function (InteractionCreate $event) use ($interaction) {
            $event = (array) $event;

            return $event['payload'] === [
                'gameId' => 'test',
                'interactionId' => $interaction['interactionId'],
                'authorizedCallers' => '*',
                'type' => GameInteractions::Vote->value,
            ];
        });

        $redisInteraction = Redis::get('game:test:interactions');
        $expectedInteraction['interactionId'] = $interaction['interactionId'];

        $this->assertSame(sort($expectedInteraction), sort($interaction));
        $this->assertSame(sort($expectedInteraction), sort($redisInteraction[0]));
    }

    public function testEndingInteraction()
    {
        Event::fake();

        $interaction = $this->service->create('otherGame', GameInteractions::Vote);
        $this->service->close('otherGame', $interaction['interactionId']);

        $this->assertEmpty(Redis::get('game:otherGame:interactions'));

        Event::assertDispatched(function (InteractionClose $event) use ($interaction) {
            return $event->payload === [
                'gameId' => 'otherGame',
                'interactionId' => $interaction['interactionId'],
            ];
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InteractionService();
    }
}
