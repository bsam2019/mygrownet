<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorDividend
{
    private function __construct(
        private readonly int $id,
        private int $investorAccountId,
        private string $dividendPeriod,
        private float $grossAmount,
        private float $taxWithheld,
        private float $netAmount,
        private ?DateTimeImmutable $declarationDate,
        private ?DateTimeImmutable $paymentDate,
        private string $status,
        private ?string $paymentMethod,
        private ?string $paymentReference,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $investorAccountId,
        string $dividendPeriod,
        float $grossAmount,
        float $taxWithheld,
        float $netAmount,
        ?DateTimeImmutable $declarationDate = null,
        ?DateTimeImmutable $paymentDate = null
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            investorAccountId: $investorAccountId,
            dividendPeriod: $dividendPeriod,
            grossAmount: $grossAmount,
            taxWithheld: $taxWithheld,
            netAmount: $netAmount,
            declarationDate: $declarationDate,
            paymentDate: $paymentDate,
            status: 'declared',
            paymentMethod: null,
            paymentReference: null,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        int $investorAccountId,
        string $dividendPeriod,
        float $grossAmount,
        float $taxWithheld,
        float $netAmount,
        ?DateTimeImmutable $declarationDate,
        ?DateTimeImmutable $paymentDate,
        string $status,
        ?string $paymentMethod,
        ?string $paymentReference,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            investorAccountId: $investorAccountId,
            dividendPeriod: $dividendPeriod,
            grossAmount: $grossAmount,
            taxWithheld: $taxWithheld,
            netAmount: $netAmount,
            declarationDate: $declarationDate,
            paymentDate: $paymentDate,
            status: $status,
            paymentMethod: $paymentMethod,
            paymentReference: $paymentReference,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function markAsPaid(string $paymentMethod, string $paymentReference): void
    {
        $this->status = 'paid';
        $this->paymentMethod = $paymentMethod;
        $this->paymentReference = $paymentReference;
        $this->paymentDate = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getInvestorAccountId(): int { return $this->investorAccountId; }
    public function getDividendPeriod(): string { return $this->dividendPeriod; }
    public function getGrossAmount(): float { return $this->grossAmount; }
    public function getTaxWithheld(): float { return $this->taxWithheld; }
    public function getNetAmount(): float { return $this->netAmount; }
    public function getDeclarationDate(): ?DateTimeImmutable { return $this->declarationDate; }
    public function getPaymentDate(): ?DateTimeImmutable { return $this->paymentDate; }
    public function getStatus(): string { return $this->status; }
    public function getPaymentMethod(): ?string { return $this->paymentMethod; }
    public function getPaymentReference(): ?string { return $this->paymentReference; }
}
