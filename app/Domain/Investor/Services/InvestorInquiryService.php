<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorInquiry;
use App\Domain\Investor\Repositories\InvestorInquiryRepositoryInterface;
use App\Domain\Investor\ValueObjects\InvestmentRange;

/**
 * Investor Inquiry Service
 * 
 * Domain service for handling investor inquiry business logic
 */
class InvestorInquiryService
{
    public function __construct(
        private readonly InvestorInquiryRepositoryInterface $repository
    ) {}

    /**
     * Create a new investor inquiry
     */
    public function createInquiry(
        string $name,
        string $email,
        string $phone,
        string $investmentRangeValue,
        ?string $message = null
    ): InvestorInquiry {
        $investmentRange = InvestmentRange::from($investmentRangeValue);
        
        $inquiry = InvestorInquiry::create(
            name: $name,
            email: $email,
            phone: $phone,
            investmentRange: $investmentRange,
            message: $message
        );

        return $this->repository->save($inquiry);
    }

    /**
     * Mark an inquiry as contacted
     */
    public function markAsContacted(int $inquiryId): InvestorInquiry
    {
        $inquiry = $this->repository->findById($inquiryId);
        
        if (!$inquiry) {
            throw new \DomainException("Inquiry not found: {$inquiryId}");
        }

        $inquiry->markAsContacted();
        
        return $this->repository->save($inquiry);
    }

    /**
     * Schedule a meeting for an inquiry
     */
    public function scheduleMeeting(int $inquiryId): InvestorInquiry
    {
        $inquiry = $this->repository->findById($inquiryId);
        
        if (!$inquiry) {
            throw new \DomainException("Inquiry not found: {$inquiryId}");
        }

        $inquiry->scheduleMeeting();
        
        return $this->repository->save($inquiry);
    }

    /**
     * Close an inquiry
     */
    public function closeInquiry(int $inquiryId): InvestorInquiry
    {
        $inquiry = $this->repository->findById($inquiryId);
        
        if (!$inquiry) {
            throw new \DomainException("Inquiry not found: {$inquiryId}");
        }

        $inquiry->close();
        
        return $this->repository->save($inquiry);
    }

    /**
     * Get all high-value inquiries
     */
    public function getHighValueInquiries(): array
    {
        return $this->repository->findHighValueInquiries();
    }
}
