<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorSurvey
{
    private function __construct(
        private readonly int $id,
        private string $title,
        private string $description,
        private string $surveyType,
        private array $questions,
        private string $status,
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate,
        private bool $isAnonymous,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        string $title,
        string $description,
        string $surveyType,
        array $questions,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        bool $isAnonymous = false
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            title: $title,
            description: $description,
            surveyType: $surveyType,
            questions: $questions,
            status: 'active',
            startDate: $startDate,
            endDate: $endDate,
            isAnonymous: $isAnonymous,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        string $title,
        string $description,
        string $surveyType,
        array $questions,
        string $status,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        bool $isAnonymous,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            title: $title,
            description: $description,
            surveyType: $surveyType,
            questions: $questions,
            status: $status,
            startDate: $startDate,
            endDate: $endDate,
            isAnonymous: $isAnonymous,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function isActive(): bool
    {
        $now = new DateTimeImmutable();
        return $this->status === 'active'
            && $this->startDate <= $now
            && $this->endDate >= $now;
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getSurveyType(): string { return $this->surveyType; }
    public function getQuestions(): array { return $this->questions; }
    public function getStatus(): string { return $this->status; }
    public function getStartDate(): DateTimeImmutable { return $this->startDate; }
    public function getEndDate(): DateTimeImmutable { return $this->endDate; }
    public function isAnonymous(): bool { return $this->isAnonymous; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}
