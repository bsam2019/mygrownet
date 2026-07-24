<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class QuarterlyReport
{
    private function __construct(
        private readonly int $id,
        private string $title,
        private int $quarter,
        private int $year,
        private ?DateTimeImmutable $publishedAt
    ) {}

    public static function fromPersistence(
        int $id,
        string $title,
        int $quarter,
        int $year,
        ?DateTimeImmutable $publishedAt
    ): self {
        return new self(
            id: $id,
            title: $title,
            quarter: $quarter,
            year: $year,
            publishedAt: $publishedAt
        );
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getQuarter(): int { return $this->quarter; }
    public function getYear(): int { return $this->year; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
}
