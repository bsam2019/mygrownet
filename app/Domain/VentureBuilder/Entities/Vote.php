<?php

namespace App\Domain\VentureBuilder\Entities;

use DateTimeImmutable;

class Vote
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $resolutionId,
        public readonly int $shareholderId,
        public readonly int $userId,
        public readonly string $vote,
        public readonly ?float $equityAtVote = null,
        public readonly ?string $comment = null,
        public readonly ?DateTimeImmutable $votedAt = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            resolutionId: (int) $data['resolution_id'],
            shareholderId: (int) $data['shareholder_id'],
            userId: (int) $data['user_id'],
            vote: $data['vote'],
            equityAtVote: array_key_exists('equity_at_vote', $data) ? (float) $data['equity_at_vote'] : null,
            comment: $data['comment'] ?? null,
            votedAt: isset($data['voted_at']) ? new \DateTimeImmutable($data['voted_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'resolution_id' => $this->resolutionId,
            'shareholder_id' => $this->shareholderId,
            'user_id' => $this->userId,
            'vote' => $this->vote,
            'equity_at_vote' => $this->equityAtVote,
            'comment' => $this->comment,
            'voted_at' => $this->votedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
