<?php

namespace App\Http\Controllers\v1\management\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function authenticate(Request $request): JsonResponse
    {
        return response()->json(['Hello' => Carbon::now()->toDateTimeString()]);
    }
}
