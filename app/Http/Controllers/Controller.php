<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    private function fromLocalNetwork(): bool
    {
        $request = request();

        if ($request->hasHeader('X-Network-Key') && $request->header('X-Network-Key') === config('app.network_key')) {
            return true;
        }

        return false;
    }
}
