<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpController extends Controller
{
    public function get(Request $request): JsonResponse
    {
        $exp = DB::table('exp')
            ->join('users', 'exp.user_id', '=', 'users.id')
            ->select('exp.*')
            ->where('exp.user_id', $request->user()->id)
            ->get();

        return response()->json(['experience' => json_decode(json_encode($exp->first()), true)['exp']]);
    }
}
