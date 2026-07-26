<?php

namespace App\Domain\Core\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class IdentityService
{
    public function currentUser(): ?User
    {
        return Auth::user();
    }

    public function currentUserId(): ?string
    {
        $user = $this->currentUser();
        return $user ? (string) $user->id : null;
    }

    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function belongsToOrganization(string $userId, string $organizationId): bool
    {
        return User::find($userId)?->organizations()
            ->where('organization_id', $organizationId)
            ->exists() ?? false;
    }

    public function getOrganizations(string $userId): array
    {
        $user = User::find($userId);
        return $user ? $user->organizations->toArray() : [];
    }
}
