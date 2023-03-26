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
        $game = $this->actingAs($user, 'api')
            ->put('/api/game', [
                'users' => [$user->id],
                'roles' => [1],
            ])
            ->json('data.game');

        $this->setCurrentUserGameActivity($user->id, $game['id']);
        $user->refresh();

        $this->assertSame($game['id'], $user->current_game);
        $this->assertSame($game['id'], $this->getCurrentUserGameActivity($user->id));
    }
}
