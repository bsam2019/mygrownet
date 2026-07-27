<?php

namespace App\Domain\PlatformPayments\Entities;

enum TransactionStatus: string
{
    case Initiated = 'initiated';
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
    case Settled = 'settled';
    case Reconciled = 'reconciled';
}

enum PaymentMethod: string
{
    case MTNMoMo = 'mtn_momo';
    case AirtelMoney = 'airtel_money';
    case MoneyUnify = 'moneyunify';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Wallet = 'wallet';
}

class PaymentTransaction
{
    private function __construct(
        private readonly ?int $id,
        private int $organizationId,
        private float $amount,
        private string $currency,
        private PaymentMethod $paymentMethod,
        private TransactionStatus $status,
        private ?string $providerTransactionId,
        private ?string $providerReference,
        private string $provider,
        private ?float $fee,
        private ?float $settledAmount,
        private ?\DateTimeImmutable $settledAt,
        private array $metadata,
        private ?string $failureReason,
        private int $attemptCount,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $organizationId,
        float $amount,
        string $currency,
        PaymentMethod $paymentMethod,
        string $provider,
        array $metadata = [],
    ): self {
        return new self(
            id: null,
            organizationId: $organizationId,
            amount: $amount,
            currency: $currency,
            paymentMethod: $paymentMethod,
            status: TransactionStatus::Initiated,
            providerTransactionId: null,
            providerReference: null,
            provider: $provider,
            fee: null,
            settledAmount: null,
            settledAt: null,
            metadata: $metadata,
            failureReason: null,
            attemptCount: 0,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        int $organizationId,
        float $amount,
        string $currency,
        string $paymentMethod,
        string $status,
        ?string $providerTransactionId,
        ?string $providerReference,
        string $provider,
        ?float $fee,
        ?float $settledAmount,
        ?\DateTimeImmutable $settledAt,
        array $metadata,
        ?string $failureReason,
        int $attemptCount,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            organizationId: $organizationId,
            amount: $amount,
            currency: $currency,
            paymentMethod: PaymentMethod::from($paymentMethod),
            status: TransactionStatus::from($status),
            providerTransactionId: $providerTransactionId,
            providerReference: $providerReference,
            provider: $provider,
            fee: $fee,
            settledAmount: $settledAmount,
            settledAt: $settledAt,
            metadata: $metadata,
            failureReason: $failureReason,
            attemptCount: $attemptCount,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function markCompleted(string $providerTransactionId, ?string $reference = null): void
    {
        $this->status = TransactionStatus::Completed;
        $this->providerTransactionId = $providerTransactionId;
        $this->providerReference = $reference;
    }

    public function markFailed(string $reason): void
    {
        $this->status = TransactionStatus::Failed;
        $this->failureReason = $reason;
        $this->attemptCount++;
    }

    public function markSettled(float $amount, float $fee, \DateTimeImmutable $settledAt): void
    {
        $this->settledAmount = $amount;
        $this->fee = $fee;
        $this->settledAt = $settledAt;
        $this->status = TransactionStatus::Settled;
    }

    public function markReconciled(): void
    {
        $this->status = TransactionStatus::Reconciled;
    }

    public function id(): ?int { return $this->id; }
    public function organizationId(): int { return $this->organizationId; }
    public function amount(): float { return $this->amount; }
    public function currency(): string { return $this->currency; }
    public function paymentMethod(): PaymentMethod { return $this->paymentMethod; }
    public function status(): TransactionStatus { return $this->status; }
    public function providerTransactionId(): ?string { return $this->providerTransactionId; }
    public function provider(): string { return $this->provider; }
    public function attemptCount(): int { return $this->attemptCount; }
    public function failureReason(): ?string { return $this->failureReason; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organizationId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->paymentMethod->value,
            'status' => $this->status->value,
            'provider_transaction_id' => $this->providerTransactionId,
            'provider_reference' => $this->providerReference,
            'provider' => $this->provider,
            'fee' => $this->fee,
            'settled_amount' => $this->settledAmount,
            'settled_at' => $this->settledAt?->format(\DateTimeInterface::ATOM),
            'failure_reason' => $this->failureReason,
            'attempt_count' => $this->attemptCount,
        ];
    }
}
