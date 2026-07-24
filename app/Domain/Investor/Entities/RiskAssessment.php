<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class RiskAssessment
{
    private function __construct(
        private readonly int $id,
        private string $riskLevel,
        private float $riskScore,
        private DateTimeImmutable $assessmentDate,
        private ?array $factors,
        private ?string $notes,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function fromPersistence(
        int $id,
        string $riskLevel,
        float $riskScore,
        DateTimeImmutable $assessmentDate,
        ?array $factors,
        ?string $notes,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            riskLevel: $riskLevel,
            riskScore: $riskScore,
            assessmentDate: $assessmentDate,
            factors: $factors,
            notes: $notes,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function getId(): int { return $this->id; }
    public function getRiskLevel(): string { return $this->riskLevel; }
    public function getRiskScore(): float { return $this->riskScore; }
    public function getAssessmentDate(): DateTimeImmutable { return $this->assessmentDate; }
    public function getFactors(): ?array { return $this->factors; }
    public function getNotes(): ?string { return $this->notes; }
}
