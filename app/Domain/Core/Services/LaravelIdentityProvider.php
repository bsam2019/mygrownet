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
        // Check main users table first
        $user = User::where('email', $email)->first();
        if ($user) {
            return $user;
        }

        // Fall back to StockFlow users
        $saUser = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel::where('email', $email)->first();
        if ($saUser) {
            return $this->syncToMainUser($saUser);
        }

        // Fall back to PrimeEdge clients
        $client = \App\Infrastructure\PrimeEdge\Persistence\ClientModel::where('email', $email)->first();
        if ($client) {
            return $this->syncToMainUser($client);
        }

        return null;
    }

    public function getProviderName(): string
    {
        return 'laravel';
    }

    private function syncToMainUser($authRecord): ?User
    {
        $user = User::where('email', $authRecord->email)->first();
        if ($user) {
            return $user;
        }

        return User::create([
            'name' => $authRecord->name,
            'email' => $authRecord->email,
            'password' => $authRecord->password,
            'status' => 'active',
        ]);
    }
}
