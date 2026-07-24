<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorSurveyResponse
{
    private function __construct(
        private readonly int $id,
        private int $surveyId,
        private ?int $investorAccountId,
        private array $responses,
        private DateTimeImmutable $submittedAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $surveyId,
        ?int $investorAccountId,
        array $responses
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            surveyId: $surveyId,
            investorAccountId: $investorAccountId,
            responses: $responses,
            submittedAt: $now,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        int $surveyId,
        ?int $investorAccountId,
        array $responses,
        DateTimeImmutable $submittedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            surveyId: $surveyId,
            investorAccountId: $investorAccountId,
            responses: $responses,
            submittedAt: $submittedAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function getId(): int { return $this->id; }
    public function getSurveyId(): int { return $this->surveyId; }
    public function getInvestorAccountId(): ?int { return $this->investorAccountId; }
    public function getResponses(): array { return $this->responses; }
    public function getSubmittedAt(): DateTimeImmutable { return $this->submittedAt; }
}
