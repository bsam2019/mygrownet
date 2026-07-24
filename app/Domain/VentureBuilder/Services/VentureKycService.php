<?php

namespace App\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Repositories\InvestmentRepositoryInterface;

class VentureKycService
{
    public function __construct(
        private readonly InvestmentRepositoryInterface $investmentRepository,
    ) {}

    public function requiresKyc(int $userId, ?array $userData = null): bool
    {
        $totalInvested = $this->investmentRepository->getTotalInvestedByUser($userId, 0);
        $tierLevel = $userData['investment_tier']['level'] ?? 0;
        return $totalInvested >= 10000 || $tierLevel >= 3;
    }

    public function isKycVerified(?array $userData): bool
    {
        return !empty($userData['id_verified_at']);
    }

    public function canInvest(int $userId, float $amount, ?array $userData): array
    {
        $issues = [];

        if ($amount >= 10000 && !$this->isKycVerified($userData)) {
            $issues[] = 'KYC verification required for investments of K10,000 or more.';
        }

        if ($this->requiresKyc($userId, $userData) && !$this->isKycVerified($userData)) {
            $issues[] = 'KYC verification required based on your investment profile.';
        }

        return $issues;
    }
}
