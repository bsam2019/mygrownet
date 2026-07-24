<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorPollVote
{
    private function __construct(
        private readonly int $id,
        private int $pollId,
        private int $investorAccountId,
        private array $selectedOptions,
        private DateTimeImmutable $votedAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $pollId,
        int $investorAccountId,
        array $selectedOptions
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            pollId: $pollId,
            investorAccountId: $investorAccountId,
            selectedOptions: $selectedOptions,
            votedAt: $now,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        int $pollId,
        int $investorAccountId,
        array $selectedOptions,
        DateTimeImmutable $votedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            pollId: $pollId,
            investorAccountId: $investorAccountId,
            selectedOptions: $selectedOptions,
            votedAt: $votedAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function getId(): int { return $this->id; }
    public function getPollId(): int { return $this->pollId; }
    public function getInvestorAccountId(): int { return $this->investorAccountId; }
    public function getSelectedOptions(): array { return $this->selectedOptions; }
    public function getVotedAt(): DateTimeImmutable { return $this->votedAt; }
}
