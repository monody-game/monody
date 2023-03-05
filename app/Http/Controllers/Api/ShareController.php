<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exp;
use App\Models\User;
use App\Services\ExpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\AbstractFont;
use Intervention\Image\AbstractShape;
use Intervention\Image\Facades\Image;
use League\Glide\Server;

class ShareController extends Controller
{
    public function index(Request $request, ExpService $expService, Server $glide): JsonResponse
    {
        /** @var User $user route is protected by api auth guard */
        $user = $request->user();
        $avatarPath = str_replace('/assets/', '', $user->avatar);

        $avatar = Image::cache(function ($image) use ($avatarPath, $user) {
            $avatar = $image->make(Storage::path($avatarPath));
            $avatar->fit(1250, 1250);
            $avatar->mask($this->createCircleMask(1250, 1250), false);
            $avatar->trim('top-left');
            $avatar->save(Storage::path("profiles/{$user->id}-avatar.temp.png"));

            Storage::delete("profiles/{$user->id}-avatar.temp.png");

            return $avatar;
        });

        Image::cache(function ($image) use ($user, $expService, $avatar, $avatarPath, $glide) {
            $glide->deleteCache(str_replace('avatars', 'profiles', $avatarPath));

            $exp = Exp::where('user_id', $user->id)->get()[0]?->exp;
            $neededExp = $expService->nextLevelExp($user->level);

            $elo = 'N/A';

            $senBold = public_path('fonts/Sen-Bold.ttf');
            $senRegular = public_path('fonts/Sen-Regular.ttf');
            $darkBackground = '0f1127';
            $darkBorder = 'rgba(15, 17, 39, .25)';
            $accentLight = 'fff5cf';

            $profile = $image->make(Storage::path('profiles/template.png'));

            $profile->insert($avatar, 'top-left', 160, 160);

            $profile->text($user->username, 1570, 160 + 190, fn (AbstractFont $font) => $font->file($senBold)->size(200)->color($darkBackground));

            $profile->text($user->level, 2340, 630 + 110, fn (AbstractFont $font) => $font->file($senRegular)->size(120)->color($darkBackground));

            $profile->text($elo, 3190, 630 + 110, fn (AbstractFont $font) => $font->file($senRegular)->size(120)->color($darkBackground));

            $profile->rectangle(1570, 970, 1570 + 2055, 970 + 240, fn (AbstractShape $shape) => $shape->border(10, $darkBorder));

            $progressLength = ($exp * (1580 + 2040)) / $neededExp;

            if ($progressLength === 0) {
                $progressLength = 1580;
            }

            $profile->rectangle(1575, 975, $progressLength, 965 + 240, fn (AbstractShape $shape) => $shape->background($accentLight));

            $profile->text($exp . '/' . $neededExp, 2477, 1015 + 110, fn (AbstractFont $font) => $font->file($senRegular)->size(120));

            $image->widen(1280);

            $profile->save(Storage::path("profiles/{$user->id}.png"));
        });

        return new JsonResponse();
    }

    public function createCircleMask(int $width, int $height): \Intervention\Image\Image
    {
        $circle = Image::canvas($width, $height);

        return $circle->circle($width - 1, $width / 2, $height / 2, fn (AbstractShape $shape) => $shape->background('fff'));
    }
}
