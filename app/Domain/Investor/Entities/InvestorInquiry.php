<?php

namespace App\Domain\Investor\Entities;

use App\Domain\Investor\ValueObjects\InvestmentRange;
use App\Domain\Investor\ValueObjects\InquiryStatus;
use DateTimeImmutable;

/**
 * Investor Inquiry Entity
 * 
 * Represents an inquiry from a potential investor
 */
class InvestorInquiry
{
    private function __construct(
        private readonly int $id,
        private string $name,
        private string $email,
        private string $phone,
        private InvestmentRange $investmentRange,
        private ?string $message,
        private InquiryStatus $status,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        string $name,
        string $email,
        string $phone,
        InvestmentRange $investmentRange,
        ?string $message = null
    ): self {
        $now = new DateTimeImmutable();
        
        return new self(
            id: 0, // Will be set by repository
            name: $name,
            email: $email,
            phone: $phone,
            investmentRange: $investmentRange,
            message: $message,
            status: InquiryStatus::new(),
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        string $name,
        string $email,
        string $phone,
        InvestmentRange $investmentRange,
        ?string $message,
        InquiryStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            phone: $phone,
            investmentRange: $investmentRange,
            message: $message,
            status: $status,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    // Business logic methods
    public function markAsContacted(): void
    {
        $this->status = InquiryStatus::contacted();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function scheduleMeeting(): void
    {
        $this->status = InquiryStatus::meetingScheduled();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function close(): void
    {
        $this->status = InquiryStatus::closed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isNew(): bool
    {
        return $this->status->equals(InquiryStatus::new());
    }

    public function isHighValue(): bool
    {
        return $this->investmentRange->isHighValue();
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getInvestmentRange(): InvestmentRange
    {
        return $this->investmentRange;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getStatus(): InquiryStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
