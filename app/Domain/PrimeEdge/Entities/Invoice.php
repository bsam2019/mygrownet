<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\InvoiceId;
use App\Domain\PrimeEdge\ValueObjects\InvoiceNumber;
use App\Domain\PrimeEdge\ValueObjects\InvoiceStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use DateTimeImmutable;

class Invoice
{
    /** @var InvoiceLineItem[] */
    private array $lineItems;

    private function __construct(
        private readonly InvoiceId $id,
        private readonly ClientId $clientId,
        private InvoiceNumber $number,
        private InvoiceStatus $status,
        private Money $total,
        private ?string $engagementId,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $sentAt,
        private ?DateTimeImmutable $paidAt,
        private ?DateTimeImmutable $updatedAt,
        array $lineItems = [],
    ) {
        $this->lineItems = $lineItems;
    }

    public static function create(
        InvoiceId $id,
        ClientId $clientId,
        InvoiceNumber $number,
        ?string $engagementId = null,
        ?string $notes = null,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            number: $number,
            status: InvoiceStatus::DRAFT,
            total: Money::zero(),
            engagementId: $engagementId,
            notes: $notes,
            createdAt: new DateTimeImmutable(),
            sentAt: null,
            paidAt: null,
            updatedAt: null,
            lineItems: [],
        );
    }

    public static function reconstitute(
        InvoiceId $id,
        ClientId $clientId,
        InvoiceNumber $number,
        InvoiceStatus $status,
        Money $total,
        ?string $engagementId,
        ?string $notes,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $sentAt,
        ?DateTimeImmutable $paidAt,
        ?DateTimeImmutable $updatedAt,
        array $lineItems = [],
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            number: $number,
            status: $status,
            total: $total,
            engagementId: $engagementId,
            notes: $notes,
            createdAt: $createdAt,
            sentAt: $sentAt,
            paidAt: $paidAt,
            updatedAt: $updatedAt,
            lineItems: $lineItems,
        );
    }

    public function addLineItem(InvoiceLineItem $item): void
    {
        if ($this->status !== InvoiceStatus::DRAFT) {
            throw new \DomainException('Cannot add line items to a non-draft invoice');
        }
        $this->lineItems[] = $item;
        $this->recalculateTotal();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeLineItem(int $index): void
    {
        if ($this->status !== InvoiceStatus::DRAFT) {
            throw new \DomainException('Cannot remove line items from a non-draft invoice');
        }
        if (isset($this->lineItems[$index])) {
            array_splice($this->lineItems, $index, 1);
            $this->recalculateTotal();
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function send(): void
    {
        if (!$this->status->canTransitionTo(InvoiceStatus::SENT)) {
            throw new \DomainException("Cannot send invoice in status {$this->status->value}");
        }
        if (count($this->lineItems) === 0) {
            throw new \DomainException('Cannot send an invoice with no line items');
        }
        $this->status = InvoiceStatus::SENT;
        $this->sentAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsPaid(): void
    {
        if (!$this->status->canTransitionTo(InvoiceStatus::PAID)) {
            throw new \DomainException("Cannot mark as paid invoice in status {$this->status->value}");
        }
        $this->status = InvoiceStatus::PAID;
        $this->paidAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsOverdue(): void
    {
        if (!$this->status->canTransitionTo(InvoiceStatus::OVERDUE)) {
            return;
        }
        $this->status = InvoiceStatus::OVERDUE;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (!$this->status->canTransitionTo(InvoiceStatus::CANCELLED)) {
            throw new \DomainException("Cannot cancel invoice in status {$this->status->value}");
        }
        $this->status = InvoiceStatus::CANCELLED;
        $this->updatedAt = new DateTimeImmutable();
    }

    private function recalculateTotal(): void
    {
        $total = Money::zero();
        foreach ($this->lineItems as $item) {
            $total = $total->add($item->total());
        }
        $this->total = $total;
    }

    public function id(): InvoiceId { return $this->id; }
    public function clientId(): ClientId { return $this->clientId; }
    public function number(): InvoiceNumber { return $this->number; }
    public function status(): InvoiceStatus { return $this->status; }
    public function total(): Money { return $this->total; }
    public function engagementId(): ?string { return $this->engagementId; }
    public function notes(): ?string { return $this->notes; }
    public function lineItems(): array { return $this->lineItems; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function sentAt(): ?DateTimeImmutable { return $this->sentAt; }
    public function paidAt(): ?DateTimeImmutable { return $this->paidAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
