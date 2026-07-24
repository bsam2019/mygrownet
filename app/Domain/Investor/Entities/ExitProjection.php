<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class ExitProjection
{
    private function __construct(
        private readonly int $id,
        private string $exitType,
        private string $title,
        private ?DateTimeImmutable $projectedDate,
        private ?float $projectedValuation,
        private ?float $projectedMultiple,
        private float $probabilityPercentage,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function fromPersistence(
        int $id,
        string $exitType,
        string $title,
        ?DateTimeImmutable $projectedDate,
        ?float $projectedValuation,
        ?float $projectedMultiple,
        float $probabilityPercentage,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            exitType: $exitType,
            title: $title,
            projectedDate: $projectedDate,
            projectedValuation: $projectedValuation,
            projectedMultiple: $projectedMultiple,
            probabilityPercentage: $probabilityPercentage,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function getId(): int { return $this->id; }
    public function getExitType(): string { return $this->exitType; }
    public function getTitle(): string { return $this->title; }
    public function getProjectedDate(): ?DateTimeImmutable { return $this->projectedDate; }
    public function getProjectedValuation(): ?float { return $this->projectedValuation; }
    public function getProjectedMultiple(): ?float { return $this->projectedMultiple; }
    public function getProbabilityPercentage(): float { return $this->probabilityPercentage; }
}
