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

class ShareController extends Controller
{
    public function index(Request $request, ExpService $expService): JsonResponse
    {
        /** @var User $user route is protected by api auth guard */
        $user = $request->user();
        $avatar = str_replace('/assets/', '', $user->avatar);
        $avatar = Image::make(Storage::path($avatar));

        Image::cache(function ($image) use ($user, $expService) {
            $exp = Exp::where('user_id', $user->id)->get()[0]?->exp;
            $neededExp = $expService->nextLevelExp($user->level);

            $elo = 'N/A';

            $senBold = public_path('fonts/Sen-Bold.ttf');
            $senRegular = public_path('fonts/Sen-Regular.ttf');
            $darkBackground = '0f1127';
            $darkBorder = 'rgba(15, 17, 39, .25)';
            $accentLight = 'fff5cf';

            $image = $image->make(Storage::path('profiles/template.png'));

            $image->circle(1250, 785, 785, function (AbstractShape $shape) use ($darkBackground) {
                // TODO: Use avatar as background (needs rework)
                $shape->background($darkBackground);
            });

            $image->text($user->username, 1570, 160 + 190, fn (AbstractFont $font) => $font->file($senBold)->size(200)->color($darkBackground));

            $image->text($user->level, 2340, 630 + 110, fn (AbstractFont $font) => $font->file($senRegular)->size(120)->color($darkBackground));

            $image->text($elo, 3190, 630 + 110, fn (AbstractFont $font) => $font->file($senRegular)->size(120)->color($darkBackground));

            $image->rectangle(1570, 970, 1570 + 2055, 970 + 240, fn (AbstractShape $shape) => $shape->border(10, $darkBorder));

            $progressLength = ($exp * (1580 + 2040)) / $neededExp;

            if ($progressLength === 0) {
                $progressLength = 1580;
            }

            $image->rectangle(1575, 975, $progressLength, 965 + 240, fn (AbstractShape $shape) => $shape->background($accentLight));

            $image->text($exp . '/' . $neededExp, 2477, 1015 + 110, fn (AbstractFont $font) => $font->file($senRegular)->size(120));

            $image->widen(1280);

            $image->save(Storage::path("profiles/{$user->id}.png"));
        });

        return new JsonResponse();
    }
}
