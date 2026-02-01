<?php

namespace App\Domain\Learning\Entities;

use App\Domain\Learning\ValueObjects\ModuleId;
use DateTimeImmutable;

class ModuleCompletion
{
    private function __construct(
        private int $userId,
        private ModuleId $moduleId,
        private DateTimeImmutable $completedAt,
        private ?DateTimeImmutable $startedAt = null,
        private ?int $timeSpentSeconds = null
    ) {}

    public static function create(
        int $userId,
        ModuleId $moduleId,
        ?DateTimeImmutable $startedAt = null
    ): self {
        return new self(
            $userId,
            $moduleId,
            new DateTimeImmutable(),
            $startedAt,
            $startedAt ? (new DateTimeImmutable())->getTimestamp() - $startedAt->getTimestamp() : null
        );
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getModuleId(): ModuleId
    {
        return $this->moduleId;
    }

    public function getCompletedAt(): DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getTimeSpentSeconds(): ?int
    {
        return $this->timeSpentSeconds;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'learning_module_id' => $this->moduleId->value(),
            'completed_at' => $this->completedAt->format('Y-m-d H:i:s'),
            'started_at' => $this->startedAt?->format('Y-m-d H:i:s'),
            'time_spent_seconds' => $this->timeSpentSeconds,
        ];
    }
}
