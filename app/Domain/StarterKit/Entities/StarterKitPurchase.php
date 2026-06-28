<?php

namespace App\Domain\StarterKit\Entities;

use App\Domain\StarterKit\ValueObjects\Money;
use App\Domain\StarterKit\ValueObjects\PurchaseStatus;
use DateTimeImmutable;
use InvalidArgumentException;

class StarterKitPurchase
{
    private function __construct(
        private readonly int $id,
        private readonly int $userId,
        private readonly string $invoiceNumber,
        private Money $amount,
        private PurchaseStatus $status,
        private readonly string $paymentMethod,
        private ?string $paymentReference,
        private readonly DateTimeImmutable $purchasedAt,
        private ?DateTimeImmutable $completedAt = null
    ) {}

    public static function create(
        int $id,
        int $userId,
        string $invoiceNumber,
        Money $amount,
        string $paymentMethod,
        ?string $paymentReference = null
    ): self {
        return new self(
            $id,
            $userId,
            $invoiceNumber,
            $amount,
            PurchaseStatus::pending(),
            $paymentMethod,
            $paymentReference,
            new DateTimeImmutable()
        );
    }

    public static function fromData(
        int $id,
        int $userId,
        string $invoiceNumber,
        Money $amount,
        PurchaseStatus $status,
        string $paymentMethod,
        ?string $paymentReference,
        DateTimeImmutable $purchasedAt,
        ?DateTimeImmutable $completedAt
    ): self {
        return new self(
            $id,
            $userId,
            $invoiceNumber,
            $amount,
            $status,
            $paymentMethod,
            $paymentReference,
            $purchasedAt,
            $completedAt
        );
    }

    // Business Logic Methods

    public function complete(): void
    {
        if ($this->status->isCompleted()) {
            throw new InvalidArgumentException('Purchase is already completed');
        }

        if ($this->status->isFailed() || $this->status->isRefunded()) {
            throw new InvalidArgumentException('Cannot complete a failed or refunded purchase');
        }

        $this->status = PurchaseStatus::completed();
        $this->completedAt = new DateTimeImmutable();
    }

    public function fail(): void
    {
        if ($this->status->isCompleted()) {
            throw new InvalidArgumentException('Cannot fail a completed purchase');
        }

        $this->status = PurchaseStatus::failed();
    }

    public function refund(): void
    {
        if (!$this->status->isCompleted()) {
            throw new InvalidArgumentException('Can only refund completed purchases');
        }

        $this->status = PurchaseStatus::refunded();
    }

    public function updatePaymentReference(string $reference): void
    {
        if ($this->status->isCompleted()) {
            throw new InvalidArgumentException('Cannot update payment reference for completed purchase');
        }

        $this->paymentReference = $reference;
    }

    public function isWalletPayment(): bool
    {
        return $this->paymentMethod === 'wallet';
    }

    public function requiresVerification(): bool
    {
        return !$this->isWalletPayment() && $this->status->isPending();
    }

    public function daysSincePurchase(): int
    {
        $now = new DateTimeImmutable();
        return $this->purchasedAt->diff($now)->days;
    }

    // Getters

    public function id(): int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function invoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function status(): PurchaseStatus
    {
        return $this->status;
    }

    public function paymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function paymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function purchasedAt(): DateTimeImmutable
    {
        return $this->purchasedAt;
    }

    public function completedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }
}
