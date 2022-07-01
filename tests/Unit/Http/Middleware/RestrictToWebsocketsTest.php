<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\RestrictToWebsockets;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RestrictToWebsocketsTest extends TestCase
{
	public function testGettingAccessWhileBeingWebHost() {
		$request = Mockery::mock(Request::class, ['getHost' => 'web']);
		$middleware = new RestrictToWebsockets();

		$res = $middleware->handle($request, function () {
			return true;
		});

		$this->assertTrue($res);
	}

	public function testGettingRejectedWhileNotBeingWebHost() {
		$middleware = new RestrictToWebsockets();
		$res = $middleware->handle(new Request(), function () {});

		$this->assertInstanceOf(JsonResponse::class, $res);
		$this->assertSame(Response::HTTP_FORBIDDEN, $res->status());
	}
}
