<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorAccount;
use App\Domain\Investor\ValueObjects\InvestorStatus;

interface InvestorAccountRepositoryInterface
{
    public function save(InvestorAccount $account): InvestorAccount;
    
    public function findById(int $id): ?InvestorAccount;
    
    public function findByUserId(int $userId): ?InvestorAccount;
    
    public function findByEmail(string $email): ?InvestorAccount;
    
    public function findByInvestmentRound(int $roundId): array;
    
    public function findByStatus(InvestorStatus $status): array;
    
    public function all(): array;
    
    public function getTotalInvestedAmount(): float;
    
    public function getInvestorCount(): int;
    
    public function delete(int $id): bool;
}
