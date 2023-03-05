<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Exp;
use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShareControllerTest extends TestCase
{
    public function testSharingProfile()
    {
        $user = User::factory()->create();
        $exp = new Exp();
        $exp->user_id = $user->id;
        $exp->exp = 10;
        $exp->save();

        Storage::assertMissing("profiles/{$user->id}.png");

        $this
            ->actingAs($user)
            ->get('/api/user/share')
            ->assertOk();

        Storage::assertExists("profiles/{$user->id}.png");
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        Storage::putFileAs('profiles', new File(storage_path('app/public/profiles/template-light.png')), 'template-light.png');
        Storage::putFileAs('avatars', new File(storage_path('app/public/avatars/default.png')), 'default.png');
    }
}
