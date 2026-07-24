<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class CompanyValuation
{
    private function __construct(
        private readonly int $id,
        private float $valuationAmount,
        private DateTimeImmutable $valuationDate,
        private string $valuationMethod,
        private ?string $notes,
        private ?array $assumptions,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function fromPersistence(
        int $id,
        float $valuationAmount,
        DateTimeImmutable $valuationDate,
        string $valuationMethod,
        ?string $notes,
        ?array $assumptions,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            valuationAmount: $valuationAmount,
            valuationDate: $valuationDate,
            valuationMethod: $valuationMethod,
            notes: $notes,
            assumptions: $assumptions,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function getId(): int { return $this->id; }
    public function getValuationAmount(): float { return $this->valuationAmount; }
    public function getValuationDate(): DateTimeImmutable { return $this->valuationDate; }
    public function getValuationMethod(): string { return $this->valuationMethod; }
    public function getNotes(): ?string { return $this->notes; }
    public function getAssumptions(): ?array { return $this->assumptions; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}
