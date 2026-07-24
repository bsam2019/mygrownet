<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorQuestionAnswer
{
    private function __construct(
        private readonly int $id,
        private int $questionId,
        private int $answeredByUserId,
        private string $answer,
        private ?array $attachments,
        private readonly DateTimeImmutable $answeredAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $questionId,
        int $answeredByUserId,
        string $answer,
        ?array $attachments = null
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            questionId: $questionId,
            answeredByUserId: $answeredByUserId,
            answer: $answer,
            attachments: $attachments,
            answeredAt: $now,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        int $questionId,
        int $answeredByUserId,
        string $answer,
        ?array $attachments,
        DateTimeImmutable $answeredAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            questionId: $questionId,
            answeredByUserId: $answeredByUserId,
            answer: $answer,
            attachments: $attachments,
            answeredAt: $answeredAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function getId(): int { return $this->id; }
    public function getQuestionId(): int { return $this->questionId; }
    public function getAnsweredByUserId(): int { return $this->answeredByUserId; }
    public function getAnswer(): string { return $this->answer; }
    public function getAttachments(): ?array { return $this->attachments; }
    public function getAnsweredAt(): DateTimeImmutable { return $this->answeredAt; }
}
