<?php

namespace App\Domain\PlatformBilling\Entities;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
}

class Invoice
{
    private function __construct(
        private readonly ?int $id,
        private int $subscriptionId,
        private int $organizationId,
        private string $invoiceNumber,
        private float $amount,
        private string $currency,
        private InvoiceStatus $status,
        private ?\DateTimeImmutable $issuedAt,
        private \DateTimeImmutable $dueDate,
        private ?\DateTimeImmutable $paidAt,
        private ?string $description,
        private array $lineItems,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $subscriptionId,
        int $organizationId,
        float $amount,
        string $currency,
        \DateTimeImmutable $dueDate,
        ?string $description = null,
        array $lineItems = [],
    ): self {
        return new self(
            id: null,
            subscriptionId: $subscriptionId,
            organizationId: $organizationId,
            invoiceNumber: '',
            amount: $amount,
            currency: $currency,
            status: InvoiceStatus::Draft,
            issuedAt: null,
            dueDate: $dueDate,
            paidAt: null,
            description: $description,
            lineItems: $lineItems,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        int $subscriptionId,
        int $organizationId,
        string $invoiceNumber,
        float $amount,
        string $currency,
        string $status,
        ?\DateTimeImmutable $issuedAt,
        \DateTimeImmutable $dueDate,
        ?\DateTimeImmutable $paidAt,
        ?string $description,
        array $lineItems,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            subscriptionId: $subscriptionId,
            organizationId: $organizationId,
            invoiceNumber: $invoiceNumber,
            amount: $amount,
            currency: $currency,
            status: InvoiceStatus::from($status),
            issuedAt: $issuedAt,
            dueDate: $dueDate,
            paidAt: $paidAt,
            description: $description,
            lineItems: $lineItems,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function issue(string $invoiceNumber): void
    {
        if ($this->status !== InvoiceStatus::Draft) {
            throw new \RuntimeException('Only draft invoices can be issued');
        }
        $this->invoiceNumber = $invoiceNumber;
        $this->status = InvoiceStatus::Issued;
        $this->issuedAt = new \DateTimeImmutable();
    }

    public function markPaid(\DateTimeImmutable $paidAt): void
    {
        if (!in_array($this->status, [InvoiceStatus::Issued, InvoiceStatus::Overdue], true)) {
            throw new \RuntimeException('Only issued or overdue invoices can be marked paid');
        }
        $this->status = InvoiceStatus::Paid;
        $this->paidAt = $paidAt;
    }

    public function markOverdue(): void
    {
        if ($this->status !== InvoiceStatus::Issued) {
            throw new \RuntimeException('Only issued invoices can become overdue');
        }
        $this->status = InvoiceStatus::Overdue;
    }

    public function cancel(): void
    {
        if (in_array($this->status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled], true)) {
            throw new \RuntimeException('Paid or already cancelled invoices cannot be cancelled');
        }
        $this->status = InvoiceStatus::Cancelled;
    }

    public function isOverdue(): bool
    {
        return $this->status === InvoiceStatus::Issued && $this->dueDate < new \DateTimeImmutable();
    }

    public function id(): ?int { return $this->id; }
    public function subscriptionId(): int { return $this->subscriptionId; }
    public function organizationId(): int { return $this->organizationId; }
    public function invoiceNumber(): string { return $this->invoiceNumber; }
    public function amount(): float { return $this->amount; }
    public function currency(): string { return $this->currency; }
    public function status(): InvoiceStatus { return $this->status; }
    public function issuedAt(): ?\DateTimeImmutable { return $this->issuedAt; }
    public function dueDate(): \DateTimeImmutable { return $this->dueDate; }
    public function paidAt(): ?\DateTimeImmutable { return $this->paidAt; }
    public function description(): ?string { return $this->description; }
    public function lineItems(): array { return $this->lineItems; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'subscription_id' => $this->subscriptionId,
            'organization_id' => $this->organizationId,
            'invoice_number' => $this->invoiceNumber,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'issued_at' => $this->issuedAt?->format(\DateTimeInterface::ATOM),
            'due_date' => $this->dueDate->format(\DateTimeInterface::ATOM),
            'paid_at' => $this->paidAt?->format(\DateTimeInterface::ATOM),
            'description' => $this->description,
            'line_items' => $this->lineItems,
        ];
    }
}
