<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\RestrictToDockerNetwork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RestrictToDockerNetworkTest extends TestCase
{
    public function testGettingAccessWhileRequestingWebHost()
    {
        $request = Mockery::mock(Request::class, ['getHost' => 'web']);
        $middleware = new RestrictToDockerNetwork();

        $res = $middleware->handle($request, function () {
            return true;
        });

        $this->assertTrue($res);
    }

    public function testGettingRejectedWhileNotRequestingWebHost()
    {
        $middleware = new RestrictToDockerNetwork();
        $res = $middleware->handle(new Request(), function () {
        });

        $this->assertInstanceOf(JsonResponse::class, $res);
        $this->assertSame(Response::HTTP_FORBIDDEN, $res->status());
    }
}
