<?php

namespace App\Domain\Core\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface MyGrowIdentity
{
    public function authenticate(string $email, string $password, Request $request): ?User;

    public function validateSession(string $token): ?User;

    public function redirectToLogin(string $returnUrl): string;

    public function getLoginUrl(): string;

    public function validateReturnUrl(string $returnUrl, string $signature, int $expires): bool;

    public function mintToken(User $user, string $deviceName): string;
}
