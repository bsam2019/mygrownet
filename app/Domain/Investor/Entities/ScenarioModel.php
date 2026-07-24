<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class ScenarioModel
{
    private function __construct(
        private readonly int $id,
        private string $name,
        private string $scenarioType,
        private ?float $projectedValuation1y,
        private ?float $projectedRoi1y,
        private ?float $projectedValuation3y,
        private ?float $projectedRoi3y,
        private ?float $projectedValuation5y,
        private ?float $projectedRoi5y,
        private ?array $assumptions,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function fromPersistence(
        int $id,
        string $name,
        string $scenarioType,
        ?float $projectedValuation1y,
        ?float $projectedRoi1y,
        ?float $projectedValuation3y,
        ?float $projectedRoi3y,
        ?float $projectedValuation5y,
        ?float $projectedRoi5y,
        ?array $assumptions,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            scenarioType: $scenarioType,
            projectedValuation1y: $projectedValuation1y,
            projectedRoi1y: $projectedRoi1y,
            projectedValuation3y: $projectedValuation3y,
            projectedRoi3y: $projectedRoi3y,
            projectedValuation5y: $projectedValuation5y,
            projectedRoi5y: $projectedRoi5y,
            assumptions: $assumptions,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getScenarioType(): string { return $this->scenarioType; }
    public function getProjectedValuation1y(): ?float { return $this->projectedValuation1y; }
    public function getProjectedRoi1y(): ?float { return $this->projectedRoi1y; }
    public function getProjectedValuation3y(): ?float { return $this->projectedValuation3y; }
    public function getProjectedRoi3y(): ?float { return $this->projectedRoi3y; }
    public function getProjectedValuation5y(): ?float { return $this->projectedValuation5y; }
    public function getProjectedRoi5y(): ?float { return $this->projectedRoi5y; }
    public function getAssumptions(): ?array { return $this->assumptions; }
}
