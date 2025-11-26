<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorAnnouncement;

interface InvestorAnnouncementRepositoryInterface
{
    public function findById(int $id): ?InvestorAnnouncement;
    
    public function findAll(): array;
    
    public function findPublished(): array;
    
    public function findActive(): array;
    
    public function findPinned(): array;
    
    public function findByType(string $type): array;
    
    public function save(InvestorAnnouncement $announcement): InvestorAnnouncement;
    
    public function delete(int $id): bool;
    
    public function markAsRead(int $announcementId, int $investorAccountId): void;
    
    public function isReadByInvestor(int $announcementId, int $investorAccountId): bool;
    
    public function getReadCount(int $announcementId): int;
    
    public function getUnreadForInvestor(int $investorAccountId): array;
}
