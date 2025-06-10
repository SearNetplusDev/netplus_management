<?php

namespace App\Http\Controllers\v1\management\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\Management\Auth\AuthRequest;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthController extends Controller
{
    public function authenticate(AuthRequest $request): JsonResponse
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status_id' => 't'])) {
            $user = Auth::user();
            $request->session()->regenerate();
//            $token = $user->createToken('authToken')->plainTextToken;
            $message = 'Authenticated successfully';

            return response()->json([
                'message' => $message,
                'user' => $user,
//                'token' => $token
            ], SymfonyResponse::HTTP_OK);
        }

        return response()->json([
            'message' => 'Invalid credentials or inactive user',
        ], SymfonyResponse::HTTP_UNAUTHORIZED);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

//        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ], SymfonyResponse::HTTP_OK);
    }
}
