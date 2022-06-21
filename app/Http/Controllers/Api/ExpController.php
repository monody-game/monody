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

        $exp = Exp::select('*')
            ->where('user_id', $user_id)
            ->get();

        $exp = $exp->first();

        if (null === $exp) {
            $exp = new Exp();
            $exp->user_id = $user_id;
            $exp->exp = 0;
            $exp->save();
        }

        return new JsonResponse(['experience' => [
            'user_id' => $exp->user_id,
            'exp' => $exp->exp,
        ]]);
    }
}
