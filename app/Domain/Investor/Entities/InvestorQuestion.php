<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorQuestion
{
    private function __construct(
        private readonly int $id,
        private int $investorAccountId,
        private string $subject,
        private string $question,
        private string $category,
        private string $status,
        private bool $isPublic,
        private int $upvotes,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $investorAccountId,
        string $subject,
        string $question,
        string $category,
        bool $isPublic = true
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            investorAccountId: $investorAccountId,
            subject: $subject,
            question: $question,
            category: $category,
            status: 'pending',
            isPublic: $isPublic,
            upvotes: 0,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        int $investorAccountId,
        string $subject,
        string $question,
        string $category,
        string $status,
        bool $isPublic,
        int $upvotes,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            investorAccountId: $investorAccountId,
            subject: $subject,
            question: $question,
            category: $category,
            status: $status,
            isPublic: $isPublic,
            upvotes: $upvotes,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function answer(): void
    {
        $this->status = 'answered';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function makePublic(): void
    {
        $this->isPublic = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function incrementUpvotes(): void
    {
        $this->upvotes++;
    }

    public function decrementUpvotes(): void
    {
        if ($this->upvotes > 0) {
            $this->upvotes--;
        }
    }

    public function getId(): int { return $this->id; }
    public function getInvestorAccountId(): int { return $this->investorAccountId; }
    public function getSubject(): string { return $this->subject; }
    public function getQuestion(): string { return $this->question; }
    public function getCategory(): string { return $this->category; }
    public function getStatus(): string { return $this->status; }
    public function isPublic(): bool { return $this->isPublic; }
    public function getUpvotes(): int { return $this->upvotes; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
