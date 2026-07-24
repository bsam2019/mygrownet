<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorFeedback
{
    private function __construct(
        private readonly int $id,
        private int $investorAccountId,
        private string $feedbackType,
        private string $category,
        private string $subject,
        private string $feedback,
        private ?int $satisfactionRating,
        private string $status,
        private ?string $adminResponse,
        private ?DateTimeImmutable $respondedAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $investorAccountId,
        string $feedbackType,
        string $category,
        string $subject,
        string $feedback,
        ?int $satisfactionRating = null
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            investorAccountId: $investorAccountId,
            feedbackType: $feedbackType,
            category: $category,
            subject: $subject,
            feedback: $feedback,
            satisfactionRating: $satisfactionRating,
            status: 'submitted',
            adminResponse: null,
            respondedAt: null,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        int $investorAccountId,
        string $feedbackType,
        string $category,
        string $subject,
        string $feedback,
        ?int $satisfactionRating,
        string $status,
        ?string $adminResponse,
        ?DateTimeImmutable $respondedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            investorAccountId: $investorAccountId,
            feedbackType: $feedbackType,
            category: $category,
            subject: $subject,
            feedback: $feedback,
            satisfactionRating: $satisfactionRating,
            status: $status,
            adminResponse: $adminResponse,
            respondedAt: $respondedAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function respond(string $response): void
    {
        $this->adminResponse = $response;
        $this->status = 'addressed';
        $this->respondedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getInvestorAccountId(): int { return $this->investorAccountId; }
    public function getFeedbackType(): string { return $this->feedbackType; }
    public function getCategory(): string { return $this->category; }
    public function getSubject(): string { return $this->subject; }
    public function getFeedback(): string { return $this->feedback; }
    public function getSatisfactionRating(): ?int { return $this->satisfactionRating; }
    public function getStatus(): string { return $this->status; }
    public function getAdminResponse(): ?string { return $this->adminResponse; }
    public function getRespondedAt(): ?DateTimeImmutable { return $this->respondedAt; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}
