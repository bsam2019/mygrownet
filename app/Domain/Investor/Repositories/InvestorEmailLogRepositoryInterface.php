<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorEmailLog;

interface InvestorEmailLogRepositoryInterface
{
    public function findById(int $id): ?InvestorEmailLog;
    
    public function save(InvestorEmailLog $log): InvestorEmailLog;
    
    /**
     * Get email logs for an investor
     * @return InvestorEmailLog[]
     */
    public function findByInvestorAccountId(int $investorAccountId, int $limit = 50): array;
    
    /**
     * Get email logs by reference
     * @return InvestorEmailLog[]
     */
    public function findByReference(string $referenceType, int $referenceId): array;
    
    /**
     * Get pending emails for sending
     * @return InvestorEmailLog[]
     */
    public function findPending(int $limit = 100): array;
    
    /**
     * Get email statistics for analytics
     */
    public function getStatistics(?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array;
    
    /**
     * Get open rate for a specific email type
     */
    public function getOpenRateByType(string $emailType): float;
    
    /**
     * Get click rate for a specific email type
     */
    public function getClickRateByType(string $emailType): float;
}
