<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorDocument;
use App\Domain\Investor\ValueObjects\DocumentCategory;
use App\Domain\Investor\ValueObjects\DocumentStatus;

interface InvestorDocumentRepositoryInterface
{
    public function findById(int $id): ?InvestorDocument;
    
    public function findByCategory(DocumentCategory $category): array;
    
    public function findVisibleToInvestor(int $investmentRoundId): array;
    
    public function findAll(): array;
    
    public function findByStatus(DocumentStatus $status): array;
    
    public function save(InvestorDocument $document): InvestorDocument;
    
    public function delete(int $id): void;
    
    public function incrementDownloadCount(int $id): void;
    
    public function logAccess(int $investorAccountId, int $documentId, string $ipAddress, string $userAgent): void;
    
    public function getAccessLog(int $documentId): array;
    
    public function getInvestorAccessHistory(int $investorAccountId): array;
    
    public function getTotalDocuments(): int;
    
    public function getTotalDownloads(): int;
    
    public function getMostDownloadedDocuments(int $limit = 10): array;
}