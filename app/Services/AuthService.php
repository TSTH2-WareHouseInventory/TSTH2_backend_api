<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(array $credentials): array
    {
        $user = $this->authRepository->findUserByEmail($credentials['email']);

        if (!$user) {
            return [
                'response_code' => 401,
                'status' => 'error',
                'message' => 'Email tidak ditemukan',
            ];
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return [
                'response_code' => 401,
                'status' => 'error',
                'message' => 'Password salah',
            ];
        }

        $accessToken = $this->authRepository->createToken($user);
        $permissions = $this->authRepository->getUserPermissions($user);
        $roles = $this->authRepository->getUserRoles($user);

        return [
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'avatar' => $user->avatar,
                    'role_id' => $user->role_id,
                ],
                'token' => $accessToken,
                'permissions' => $permissions,
                'roles' => $roles,
            ],
        ];
    }

    public function logout($user): array
    {
        try {
            if (!$user) {
                return [
                    'response_code' => 401,
                    'status' => 'error',
                    'message' => 'User not authenticated',
                ];
            }

            $this->authRepository->revokeTokens($user);

            return [
                'response_code' => 200,
                'status' => 'success',
                'message' => 'Logout successful',
            ];
        } catch (\Exception $e) {
            Log::error($e);
            return [
                'response_code' => 500,
                'status' => 'error',
                'message' => 'Failed to logout',
            ];
        }
    }
}
