<?php

namespace App\Repositories;

use App\Models\User;
use Laravel\Passport\Token;

class AuthRepository
{
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function createToken(User $user): string
    {
        return $user->createToken('authToken')->accessToken;
    }

    public function getUserPermissions(User $user): \Illuminate\Support\Collection
    {
        return $user->getAllPermissions()->pluck('name');
    }

    public function getUserRoles(User $user): \Illuminate\Support\Collection
    {
        return $user->getRoleNames();
    }

    public function revokeTokens(User $user): void
    {
        Token::where('user_id', $user->id)->update(['revoked' => true]);
    }
}
