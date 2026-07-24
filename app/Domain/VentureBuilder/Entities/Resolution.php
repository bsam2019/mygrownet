<?php

namespace App\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\ValueObjects\ResolutionStatus;
use DateTimeImmutable;

class Resolution
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $ventureId,
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $type = null,
        public readonly ResolutionStatus $status,
        public readonly ?DateTimeImmutable $votingStartsAt = null,
        public readonly ?DateTimeImmutable $votingEndsAt = null,
        public readonly ?float $passThresholdPercentage = null,
        public readonly ?float $votesFor = null,
        public readonly ?float $votesAgainst = null,
        public readonly ?float $votesAbstain = null,
        public readonly ?float $totalVotedEquity = null,
        public readonly ?string $resultNotes = null,
        public readonly ?int $createdBy = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isOpenForVoting(): bool
    {
        if (!$this->status->isVoting()) {
            return false;
        }

        $now = new DateTimeImmutable();

        if ($this->votingStartsAt !== null && $this->votingStartsAt > $now) {
            return false;
        }

        if ($this->votingEndsAt !== null && $this->votingEndsAt < $now) {
            return false;
        }

        return true;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            ventureId: (int) $data['venture_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            type: $data['type'] ?? null,
            status: ResolutionStatus::fromString($data['status'] ?? 'draft'),
            votingStartsAt: isset($data['voting_starts_at']) ? new \DateTimeImmutable($data['voting_starts_at']) : null,
            votingEndsAt: isset($data['voting_ends_at']) ? new \DateTimeImmutable($data['voting_ends_at']) : null,
            passThresholdPercentage: array_key_exists('pass_threshold_percentage', $data) ? (float) $data['pass_threshold_percentage'] : null,
            votesFor: array_key_exists('votes_for', $data) ? (float) $data['votes_for'] : null,
            votesAgainst: array_key_exists('votes_against', $data) ? (float) $data['votes_against'] : null,
            votesAbstain: array_key_exists('votes_abstain', $data) ? (float) $data['votes_abstain'] : null,
            totalVotedEquity: array_key_exists('total_voted_equity', $data) ? (float) $data['total_voted_equity'] : null,
            resultNotes: $data['result_notes'] ?? null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'venture_id' => $this->ventureId,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status->value(),
            'voting_starts_at' => $this->votingStartsAt?->format('Y-m-d H:i:s'),
            'voting_ends_at' => $this->votingEndsAt?->format('Y-m-d H:i:s'),
            'pass_threshold_percentage' => $this->passThresholdPercentage,
            'votes_for' => $this->votesFor,
            'votes_against' => $this->votesAgainst,
            'votes_abstain' => $this->votesAbstain,
            'total_voted_equity' => $this->totalVotedEquity,
            'result_notes' => $this->resultNotes,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
