<?php

namespace Tests\Unit\Http\Controllers\Api\Oauth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DiscordOauthControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private string $fakeId = "1298109238";

	public function testUnlinkingDiscordAccount()
	{
		$this->assertSame($this->fakeId, $this->user['discord_id']);

		$this
			->actingAs($this->user, 'api')
			->post('/api/oauth/unlink/discord')
			->assertStatus(Response::HTTP_NO_CONTENT);

		$user = $this->user->refresh();

		$this->assertNull($user['discord_id']);
		$this->assertNull($user['discord_token']);
		$this->assertNull($user['discord_refresh_token']);
	}

	public function testUnlinkingAccountWithoutBeingLinked()
	{
		$user = $this->user;
		$user['discord_id'] = null;

		$this
			->actingAs($user, 'api')
			->post('/api/oauth/unlink/discord')
			->assertStatus(Response::HTTP_FORBIDDEN);
	}

	public function testCreatingLink() {
		$this
			->actingAs($this->user, 'api')
			->get('/api/oauth/link/discord')
			->assertRedirect();
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->makeOne([
			'discord_id' => $this->fakeId
 		]);
	}
}
