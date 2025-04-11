<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->update(['revoked' => true]);
    }
}
