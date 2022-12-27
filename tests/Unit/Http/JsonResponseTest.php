<?php

namespace Http;

use App\Enums\AlertType;
use App\Enums\Roles;
use App\Http\JsonResponse;
use Tests\TestCase;

class JsonResponseTest extends TestCase
{
    public function testAddingContent()
    {
        $res = new JsonResponse();

        $res->withContent([
            'Test content',
            true,
        ]);

        $this->assertSame([
            'content' => [
                'Test content',
                true,
            ],
        ], $res->getData(true));
    }

    public function testAddingAlerts()
    {
        $res = new JsonResponse();
        $res->withAlert(AlertType::Error, 'Test alert !');

        $this->assertSame([
            'alerts' => [
                AlertType::Error->value => 'Test alert !',
            ],
        ], $res->getData(true));
    }

    public function testAddingMessage()
    {
        $res = new JsonResponse();
        $res->withMessage('Test message !');

        $this->assertSame([
            'message' => 'Test message !',
        ], $res->getData(true));
    }

    public function testAddingMultipleKeys()
    {
        $res = new JsonResponse();
        $res
            ->withContent([
                Roles::Psychic,
            ])
            ->withAlert(AlertType::Info, 'Hey !');

        $this->assertSame([
            'content' => [Roles::Psychic->value],
            'alerts' => [
                AlertType::Info->value => 'Hey !',
            ],
        ], $res->getData(true));
    }

    public function testAddingMultipleAlerts()
    {
        $res = new JsonResponse();
        $res
            ->withAlert(AlertType::Error, 'Test')
            ->withAlert(AlertType::Success, 'Success alert');

        $this->assertSame([
            'alerts' => [
                AlertType::Error->value => 'Test',
                AlertType::Success->value => 'Success alert',
            ],
        ], $res->getData(true));
    }
}
