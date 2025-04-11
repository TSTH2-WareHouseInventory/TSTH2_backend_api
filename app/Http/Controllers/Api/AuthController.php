<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Requests\RefreshTokenRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return response()->json([
                'response_code' => '401',
                'status'        => 'error',
                'message'       => 'Email tidak ditemukan',
            ], 401);
        }
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'response_code' => '401',
                'status'        => 'error',
                'message'       => 'Password salah',
            ], 401);
        }

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'response_code' => '200',
            'status'        => 'success',
            'message'       => 'Login berhasil',
            'data' => [
                'user'  => $user,
                'token' => $accessToken,
            ],
        ], 200);
    }



    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user) {
                Token::where('user_id', $user->id)->update(['revoked' => true]);

                return response()->json([
                    'response_code' => '200',
                    'status'        => 'success',
                    'message'       => 'Logout successful',
                ], 200);
            }

            return response()->json([
                'response_code' => '401',
                'status'        => 'error',
                'message'       => 'User not authenticated',
            ], 401);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'response_code' => '500',
                'status'        => 'error',
                'message'       => 'Failed to logout',
            ], 500);
        }
    }
}
