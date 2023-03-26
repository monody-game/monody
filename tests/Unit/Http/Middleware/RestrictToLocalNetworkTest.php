<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\RestrictToLocalNetwork;
use App\Http\Responses\JsonApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RestrictToLocalNetworkTest extends TestCase
{
    public function testGettingAccess()
    {
        $middleware = new RestrictToLocalNetwork();
        $request = new Request();
        $request->headers->add([
            'X-Network-Key' => config('app.network_key'),
        ]);

        $res = $middleware->handle($request, function () {
            return true;
        });

        $this->assertTrue($res);
    }

    public function testGettingRejected()
    {
        $middleware = new RestrictToLocalNetwork();
        $res = $middleware->handle(new Request(), function () {
        });

        $this->assertInstanceOf(JsonApiResponse::class, $res);
        $this->assertSame(Response::HTTP_FORBIDDEN, $res->status->value);
    }
}
