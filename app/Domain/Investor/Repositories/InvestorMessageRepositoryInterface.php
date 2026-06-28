<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorMessage;

interface InvestorMessageRepositoryInterface
{
    public function save(InvestorMessage $message): InvestorMessage;
    
    public function findById(int $id): ?InvestorMessage;
    
    public function findByInvestorAccountId(int $investorAccountId, int $limit = 50, int $offset = 0): array;
    
    public function findConversation(int $investorAccountId, int $limit = 50): array;
    
    public function getUnreadCountForInvestor(int $investorAccountId): int;
    
    public function getUnreadCountForAdmin(): int;
    
    public function markAsRead(int $messageId): void;
    
    public function markAllAsReadForInvestor(int $investorAccountId): void;
    
    public function getAllForAdmin(int $limit = 50, int $offset = 0): array;
    
    public function delete(int $id): void;
}
