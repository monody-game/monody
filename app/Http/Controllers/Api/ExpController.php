<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        /** @var string $user_id */
        $user_id = $request->user()?->id;

        $exp = Exp::firstOrCreate(['user_id' => $user_id]);

        return new JsonResponse([
            'user_id' => $exp->user_id,
            'exp' => $exp->exp,
        ]);
    }
}
