<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MacroServiceProvider extends ServiceProvider
{
    public function register()
    {
        Str::macro('obfuscateEmail', function (string $email, int $stars = 3) {
            $at = mb_strpos($email, '@');
            if ($at - 2 > $stars) {
                $stars = $at - 2;
            }

            return mb_substr($email, 0, 1) . str_repeat('*', $stars) . mb_substr($email, $at - 1);
        });
    }
}
