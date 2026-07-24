<?php

namespace App\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\ValueObjects\TransferStatus;
use DateTimeImmutable;

class ShareTransfer
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $ventureId,
        public readonly int $fromUserId,
        public readonly int $toUserId,
        public readonly float $shares,
        public readonly ?float $pricePerShare = null,
        public readonly ?float $totalValue = null,
        public readonly TransferStatus $status,
        public readonly ?string $reason = null,
        public readonly ?string $adminNotes = null,
        public readonly ?int $approvedBy = null,
        public readonly ?DateTimeImmutable $approvedAt = null,
        public readonly ?DateTimeImmutable $completedAt = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function isCompleted(): bool
    {
        return $this->status->value() === 'approved';
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            ventureId: (int) $data['venture_id'],
            fromUserId: (int) $data['from_user_id'],
            toUserId: (int) $data['to_user_id'],
            shares: (float) $data['shares'],
            pricePerShare: array_key_exists('price_per_share', $data) ? (float) $data['price_per_share'] : null,
            totalValue: array_key_exists('total_value', $data) ? (float) $data['total_value'] : null,
            status: TransferStatus::fromString($data['status'] ?? 'pending'),
            reason: $data['reason'] ?? null,
            adminNotes: $data['admin_notes'] ?? null,
            approvedBy: isset($data['approved_by']) ? (int) $data['approved_by'] : null,
            approvedAt: isset($data['approved_at']) ? new \DateTimeImmutable($data['approved_at']) : null,
            completedAt: isset($data['completed_at']) ? new \DateTimeImmutable($data['completed_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'venture_id' => $this->ventureId,
            'from_user_id' => $this->fromUserId,
            'to_user_id' => $this->toUserId,
            'shares' => $this->shares,
            'price_per_share' => $this->pricePerShare,
            'total_value' => $this->totalValue,
            'status' => $this->status->value(),
            'reason' => $this->reason,
            'admin_notes' => $this->adminNotes,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
