<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exp;
use App\Models\User;
use App\Services\ExpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpController extends Controller
{
    public function get(Request $request, ExpService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $exp = Exp::firstOrCreate(['user_id' => $user->id]);

        return new JsonResponse([
            'user_id' => $exp->user_id,
            'exp' => $exp->exp ?? 0,
            'next_level' => $service->nextLevelExp($user->level),
        ]);
    }
}
