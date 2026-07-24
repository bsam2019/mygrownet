<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorPoll
{
    private function __construct(
        private readonly int $id,
        private string $question,
        private array $options,
        private string $pollType,
        private DateTimeImmutable $endDate,
        private bool $allowMultiple,
        private int $voteCount,
        private ?array $results,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        string $question,
        array $options,
        string $pollType,
        DateTimeImmutable $endDate,
        bool $allowMultiple = false
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            question: $question,
            options: $options,
            pollType: $pollType,
            endDate: $endDate,
            allowMultiple: $allowMultiple,
            voteCount: 0,
            results: null,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        string $question,
        array $options,
        string $pollType,
        DateTimeImmutable $endDate,
        bool $allowMultiple,
        int $voteCount,
        ?array $results,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            question: $question,
            options: $options,
            pollType: $pollType,
            endDate: $endDate,
            allowMultiple: $allowMultiple,
            voteCount: $voteCount,
            results: $results,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function isActive(): bool
    {
        return $this->endDate >= new DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getQuestion(): string { return $this->question; }
    public function getOptions(): array { return $this->options; }
    public function getPollType(): string { return $this->pollType; }
    public function getEndDate(): DateTimeImmutable { return $this->endDate; }
    public function allowMultiple(): bool { return $this->allowMultiple; }
    public function getVoteCount(): int { return $this->voteCount; }
    public function getResults(): ?array { return $this->results; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}
