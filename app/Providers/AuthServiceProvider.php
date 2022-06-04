<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('vote', function (User $user, string $gameId) {
            $game = json_decode(Redis::get("game:$gameId"), true);

            return \in_array($user->id, $game['users'], true) ? Response::allow() : Response::deny('You should be in the game if you want to vote');
        });

        Passport::routes();
    }
}
