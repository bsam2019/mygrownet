<?php

namespace App\Domain\LifePlus\Entities;

class LifePlusHabit
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly string $icon,
        public readonly string $color,
        public readonly string $frequency,
        public readonly ?string $reminderTime,
        public readonly bool $isActive,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            name: $data['name'],
            icon: $data['icon'] ?? '⭐',
            color: $data['color'] ?? '#10b981',
            frequency: $data['frequency'] ?? 'daily',
            reminderTime: $data['reminder_time'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'name' => $this->name,
            'icon' => $this->icon,
            'color' => $this->color,
            'frequency' => $this->frequency,
            'reminder_time' => $this->reminderTime,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
