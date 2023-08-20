<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Exp;
use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShareProfileControllerTest extends TestCase
{
    public function testSharingProfile()
    {
        $user = User::factory()->create();
        $exp = new Exp();
        $exp->user_id = $user->id;
        $exp->exp = 10;
        $exp->save();

        Storage::assertMissing("profiles/$user->id.png");

        $this
            ->actingAs($user)
            ->get('/api/user/share')
            ->assertNoContent();

        Storage::assertExists("profiles/$user->id.png");
        Storage::assertMissing("profiles/$user->id-avatar.temp.png");
        Storage::delete("profiles/$user->id.png");
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        Storage::putFileAs('profiles', new File(storage_path('app/public/profiles/template-light.png')), 'template-light.png');
        Storage::putFileAs('avatars', new File(storage_path('app/public/avatars/default_1.png')), 'default_1.png');
    }
}
