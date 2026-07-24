<?php

namespace App\Domain\BizBoost\Entities;

class FollowUpReminder
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?int $customerId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $dueDate,
        public readonly string $dueTime,
        public readonly string $remindAt,
        public readonly string $reminderType,
        public readonly string $priority,
        public readonly string $status,
        public readonly bool $notificationSent,
        public readonly ?string $completedAt,
        public readonly ?string $completionNotes,
        public readonly int $snoozedCount,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            title: $data['title'],
            description: $data['description'] ?? null,
            dueDate: $data['due_date'],
            dueTime: $data['due_time'] ?? '09:00',
            remindAt: $data['remind_at'] ?? "{$data['due_date']} 09:00:00",
            reminderType: $data['reminder_type'],
            priority: $data['priority'],
            status: $data['status'] ?? 'pending',
            notificationSent: (bool) ($data['notification_sent'] ?? false),
            completedAt: $data['completed_at'] ?? null,
            completionNotes: $data['completion_notes'] ?? null,
            snoozedCount: (int) ($data['snoozed_count'] ?? 0),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'customer_id' => $this->customerId,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->dueDate,
            'due_time' => $this->dueTime,
            'remind_at' => $this->remindAt,
            'reminder_type' => $this->reminderType,
            'priority' => $this->priority,
            'status' => $this->status,
            'notification_sent' => $this->notificationSent,
            'completed_at' => $this->completedAt,
            'completion_notes' => $this->completionNotes,
            'snoozed_count' => $this->snoozedCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}