<?php

namespace App\Domain\Core\Contracts;

use App\Models\User;

interface IdentityProvider
{
    public function authenticate(string $email, string $password): ?User;
    public function getUserByEmail(string $email): ?User;
    public function getProviderName(): string;
}
