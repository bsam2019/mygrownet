<?php

namespace App\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\ValueObjects\InvestmentStatus;
use DateTimeImmutable;

class Investment
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $ventureId,
        public readonly int $userId,
        public readonly float $amount,
        public readonly ?float $sharesAllocated = null,
        public readonly ?float $equityPercentage = null,
        public readonly InvestmentStatus $status,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $paymentReference = null,
        public readonly ?DateTimeImmutable $paymentConfirmedAt = null,
        public readonly ?bool $isShareholder = null,
        public readonly ?DateTimeImmutable $shareholderRegisteredAt = null,
        public readonly ?string $shareholderCertificateNumber = null,
        public readonly ?string $notes = null,
        public readonly ?int $processedBy = null,
        public readonly ?DateTimeImmutable $processedAt = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isConfirmed(): bool
    {
        return $this->status->isConfirmed();
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            ventureId: (int) $data['venture_id'],
            userId: (int) $data['user_id'],
            amount: (float) $data['amount'],
            sharesAllocated: array_key_exists('shares_allocated', $data) ? (float) $data['shares_allocated'] : null,
            equityPercentage: array_key_exists('equity_percentage', $data) ? (float) $data['equity_percentage'] : null,
            status: InvestmentStatus::fromString($data['status'] ?? 'pending'),
            paymentMethod: $data['payment_method'] ?? null,
            paymentReference: $data['payment_reference'] ?? null,
            paymentConfirmedAt: isset($data['payment_confirmed_at']) ? new \DateTimeImmutable($data['payment_confirmed_at']) : null,
            isShareholder: isset($data['is_shareholder']) ? (bool) $data['is_shareholder'] : null,
            shareholderRegisteredAt: isset($data['shareholder_registered_at']) ? new \DateTimeImmutable($data['shareholder_registered_at']) : null,
            shareholderCertificateNumber: $data['shareholder_certificate_number'] ?? null,
            notes: $data['notes'] ?? null,
            processedBy: isset($data['processed_by']) ? (int) $data['processed_by'] : null,
            processedAt: isset($data['processed_at']) ? new \DateTimeImmutable($data['processed_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'venture_id' => $this->ventureId,
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'shares_allocated' => $this->sharesAllocated,
            'equity_percentage' => $this->equityPercentage,
            'status' => $this->status->value(),
            'payment_method' => $this->paymentMethod,
            'payment_reference' => $this->paymentReference,
            'payment_confirmed_at' => $this->paymentConfirmedAt?->format('Y-m-d H:i:s'),
            'is_shareholder' => $this->isShareholder,
            'shareholder_registered_at' => $this->shareholderRegisteredAt?->format('Y-m-d H:i:s'),
            'shareholder_certificate_number' => $this->shareholderCertificateNumber,
            'notes' => $this->notes,
            'processed_by' => $this->processedBy,
            'processed_at' => $this->processedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
