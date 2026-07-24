<?php

namespace App\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\ValueObjects\DividendStatus;
use DateTimeImmutable;

class Dividend
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $ventureId,
        public readonly int $shareholderId,
        public readonly ?string $dividendPeriod = null,
        public readonly ?DateTimeImmutable $declarationDate = null,
        public readonly float $amount,
        public readonly ?float $equityPercentageAtPayment = null,
        public readonly DividendStatus $status,
        public readonly ?string $notes = null,
        public readonly ?DateTimeImmutable $paymentDate = null,
        public readonly ?DateTimeImmutable $paidAt = null,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $paymentReference = null,
        public readonly ?int $processedBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }

    public function isPending(): bool
    {
        return $this->status->isDeclared();
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            ventureId: (int) $data['venture_id'],
            shareholderId: (int) $data['shareholder_id'],
            dividendPeriod: $data['dividend_period'] ?? null,
            declarationDate: isset($data['declaration_date']) ? new \DateTimeImmutable($data['declaration_date']) : null,
            amount: (float) $data['amount'],
            equityPercentageAtPayment: array_key_exists('equity_percentage_at_payment', $data) ? (float) $data['equity_percentage_at_payment'] : null,
            status: DividendStatus::fromString($data['status'] ?? 'declared'),
            notes: $data['notes'] ?? null,
            paymentDate: isset($data['payment_date']) ? new \DateTimeImmutable($data['payment_date']) : null,
            paidAt: isset($data['paid_at']) ? new \DateTimeImmutable($data['paid_at']) : null,
            paymentMethod: $data['payment_method'] ?? null,
            paymentReference: $data['payment_reference'] ?? null,
            processedBy: isset($data['processed_by']) ? (int) $data['processed_by'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'venture_id' => $this->ventureId,
            'shareholder_id' => $this->shareholderId,
            'dividend_period' => $this->dividendPeriod,
            'declaration_date' => $this->declarationDate?->format('Y-m-d H:i:s'),
            'amount' => $this->amount,
            'equity_percentage_at_payment' => $this->equityPercentageAtPayment,
            'status' => $this->status->value(),
            'notes' => $this->notes,
            'payment_date' => $this->paymentDate?->format('Y-m-d H:i:s'),
            'paid_at' => $this->paidAt?->format('Y-m-d H:i:s'),
            'payment_method' => $this->paymentMethod,
            'payment_reference' => $this->paymentReference,
            'processed_by' => $this->processedBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
