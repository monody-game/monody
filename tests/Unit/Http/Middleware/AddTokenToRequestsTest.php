<?php

namespace Http\Middleware;

use App\Http\Middleware\AddTokenToRequests;
use Illuminate\Http\Request;
use Tests\TestCase;

class AddTokenToRequestsTest extends TestCase
{
	public function testShouldAddAuthorizationHeader() {
		$request = new Request();
		$request->cookies->set('monody_access_token', 'TestToken');

		$middleware = new AddTokenToRequests();
		$middleware->handle($request, function (Request $request) {
			$this->assertTrue($request->hasHeader('Authorization'));
			$this->assertSame('Bearer TestToken', $request->header('Authorization'));
		});
	}
}
