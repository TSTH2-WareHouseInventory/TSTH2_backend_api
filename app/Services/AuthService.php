<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function attemptLogin(array $credentials): array
    {
        $user = $this->authRepository->findUserByEmail($credentials['email']);

        if (!$user) {
            throw ValidationException::withMessages(['email' => ['Email tidak ditemukan.']]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => ['Password salah.']]);
        }

        $token = $user->createToken('authToken')->accessToken;

        return compact('user', 'token');
    }

    public function logout(User $user): void
    {
        $this->authRepository->revokeAllTokens($user);
    }
}
