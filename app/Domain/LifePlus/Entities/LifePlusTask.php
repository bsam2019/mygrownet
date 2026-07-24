<?php

namespace App\Domain\LifePlus\Entities;

class LifePlusTask
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $priority,
        public readonly ?\DateTimeImmutable $dueDate,
        public readonly ?string $dueTime,
        public readonly bool $isCompleted,
        public readonly ?\DateTimeImmutable $completedAt,
        public readonly bool $isSynced,
        public readonly ?string $localId,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            priority: $data['priority'] ?? 'medium',
            dueDate: isset($data['due_date']) ? new \DateTimeImmutable($data['due_date']) : null,
            dueTime: $data['due_time'] ?? null,
            isCompleted: (bool) ($data['is_completed'] ?? false),
            completedAt: isset($data['completed_at']) ? new \DateTimeImmutable($data['completed_at']) : null,
            isSynced: (bool) ($data['is_synced'] ?? true),
            localId: $data['local_id'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'due_time' => $this->dueTime,
            'is_completed' => $this->isCompleted,
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'is_synced' => $this->isSynced,
            'local_id' => $this->localId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
