<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\IdentityProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LaravelIdentityProvider implements IdentityProvider
{
    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->getUserByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }
        return $user;
    }

    public function getUserByEmail(string $email): ?User
    {
        // StockFlow sa_users table was dropped in Phase 8d — only platform users table exists
        return User::where('email', $email)->first();
    }

    public function getProviderName(): string
    {
        return 'laravel';
    }
}
