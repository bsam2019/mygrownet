<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorInquiry;
use App\Domain\Investor\ValueObjects\InquiryStatus;

/**
 * Investor Inquiry Repository Interface
 * 
 * Defines the contract for persisting and retrieving investor inquiries
 */
interface InvestorInquiryRepositoryInterface
{
    /**
     * Save an investor inquiry
     */
    public function save(InvestorInquiry $inquiry): InvestorInquiry;

    /**
     * Find an inquiry by ID
     */
    public function findById(int $id): ?InvestorInquiry;

    /**
     * Find all inquiries with a specific status
     */
    public function findByStatus(InquiryStatus $status): array;

    /**
     * Find all high-value inquiries
     */
    public function findHighValueInquiries(): array;

    /**
     * Get all inquiries (paginated)
     */
    public function getAll(int $page = 1, int $perPage = 20): array;

    /**
     * Count inquiries by status
     */
    public function countByStatus(InquiryStatus $status): int;
}
