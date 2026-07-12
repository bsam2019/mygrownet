<?php

namespace App\Services\VentureBuilder;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class VentureKycService
{
    public function requiresKyc(User $user): bool
    {
        $totalInvested = $user->ventureInvestments()->confirmed()->sum('amount');
        return $totalInvested >= 10000 || $user->investment_tier && $user->investment_tier->level >= 3;
    }

    public function isKycVerified(User $user): bool
    {
        return !empty($user->id_verified_at);
    }

    public function canInvest(User $user, float $amount): array
    {
        $issues = [];

        if ($amount >= 10000 && !$this->isKycVerified($user)) {
            $issues[] = 'KYC verification required for investments of K10,000 or more.';
        }

        if ($this->requiresKyc($user) && !$this->isKycVerified($user)) {
            $issues[] = 'KYC verification required based on your investment profile.';
        }

        return $issues;
    }
}
