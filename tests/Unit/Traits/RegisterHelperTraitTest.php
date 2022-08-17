<?php

namespace Tests\Unit\Traits;

use App\Models\User;
use App\Traits\RegisterHelperTrait;
use Tests\TestCase;

class RegisterHelperTraitTest extends TestCase
{
    use RegisterHelperTrait;

    public function testGettingAndSettingActivity()
    {
        $user = User::factory()->create();
        $game = json_decode(
            $this
            ->actingAs($user, 'api')
            ->post('/api/game/new', [
                'users' => [$user->id],
                'roles' => [1],
            ]
            )->getContent(), true)['game'];

        $this->setCurrentUserGameActivity($user->id, $game['id']);
        $user->refresh();

        $this->assertSame($game['id'], $user->current_game);
        $this->assertSame($game['id'], $this->getCurrentUserGameActivity($user->id));
    }
}
