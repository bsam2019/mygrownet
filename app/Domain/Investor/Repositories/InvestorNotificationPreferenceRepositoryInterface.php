<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorNotificationPreference;

interface InvestorNotificationPreferenceRepositoryInterface
{
    public function findById(int $id): ?InvestorNotificationPreference;
    
    public function findByInvestorAccountId(int $investorAccountId): ?InvestorNotificationPreference;
    
    public function findOrCreateForInvestor(int $investorAccountId): InvestorNotificationPreference;
    
    public function save(InvestorNotificationPreference $preference): InvestorNotificationPreference;
    
    /**
     * Get all investors who should receive a specific email type
     * @return InvestorNotificationPreference[]
     */
    public function findInvestorsForEmailType(string $emailType, bool $isUrgent = false): array;
    
    /**
     * Get investors with digest frequency
     * @return InvestorNotificationPreference[]
     */
    public function findByDigestFrequency(string $frequency): array;
}
