<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\InquiryId;
use App\Domain\PrimeEdge\ValueObjects\InquiryStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use DateTimeImmutable;

class Inquiry
{
    private function __construct(
        private readonly InquiryId $id,
        private readonly ClientId $clientId,
        private string $serviceDescription,
        private ?string $preferredServiceType,
        private InquiryStatus $status,
        private ?Money $quotedAmount,
        private ?string $quoteNotes,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $quotedAt,
        private ?DateTimeImmutable $respondedAt,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        InquiryId $id,
        ClientId $clientId,
        string $serviceDescription,
        ?string $preferredServiceType = null,
        ?string $notes = null,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            serviceDescription: $serviceDescription,
            preferredServiceType: $preferredServiceType,
            status: InquiryStatus::NEW,
            quotedAmount: null,
            quoteNotes: null,
            notes: $notes,
            createdAt: new DateTimeImmutable(),
            quotedAt: null,
            respondedAt: null,
            updatedAt: null,
        );
    }

    public static function reconstitute(
        InquiryId $id,
        ClientId $clientId,
        string $serviceDescription,
        ?string $preferredServiceType,
        InquiryStatus $status,
        ?Money $quotedAmount,
        ?string $quoteNotes,
        ?string $notes,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $quotedAt,
        ?DateTimeImmutable $respondedAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            serviceDescription: $serviceDescription,
            preferredServiceType: $preferredServiceType,
            status: $status,
            quotedAmount: $quotedAmount,
            quoteNotes: $quoteNotes,
            notes: $notes,
            createdAt: $createdAt,
            quotedAt: $quotedAt,
            respondedAt: $respondedAt,
            updatedAt: $updatedAt,
        );
    }

    public function sendQuote(Money $amount, string $notes = ''): void
    {
        if (!$this->status->canTransitionTo(InquiryStatus::QUOTED)) {
            throw new \DomainException("Cannot send quote for inquiry in status {$this->status->value}");
        }
        $this->status = InquiryStatus::QUOTED;
        $this->quotedAmount = $amount;
        $this->quoteNotes = $notes;
        $this->quotedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function accept(): void
    {
        if (!$this->status->canTransitionTo(InquiryStatus::ACCEPTED)) {
            throw new \DomainException("Cannot accept inquiry in status {$this->status->value}");
        }
        $this->status = InquiryStatus::ACCEPTED;
        $this->respondedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function decline(?string $reason = null): void
    {
        if (!$this->status->canTransitionTo(InquiryStatus::DECLINED)) {
            throw new \DomainException("Cannot decline inquiry in status {$this->status->value}");
        }
        $this->status = InquiryStatus::DECLINED;
        $this->quoteNotes = $reason;
        $this->respondedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function expire(): void
    {
        if (!$this->status->canTransitionTo(InquiryStatus::EXPIRED)) {
            return;
        }
        $this->status = InquiryStatus::EXPIRED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): InquiryId { return $this->id; }
    public function clientId(): ClientId { return $this->clientId; }
    public function serviceDescription(): string { return $this->serviceDescription; }
    public function preferredServiceType(): ?string { return $this->preferredServiceType; }
    public function status(): InquiryStatus { return $this->status; }
    public function quotedAmount(): ?Money { return $this->quotedAmount; }
    public function quoteNotes(): ?string { return $this->quoteNotes; }
    public function notes(): ?string { return $this->notes; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function quotedAt(): ?DateTimeImmutable { return $this->quotedAt; }
    public function respondedAt(): ?DateTimeImmutable { return $this->respondedAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
