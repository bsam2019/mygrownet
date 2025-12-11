<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\ValueObjects;

final class JourneyProgress
{
    private function __construct(
        private readonly float $overall,
        private readonly array $stageProgress,
        private readonly int $tasksCompleted,
        private readonly int $totalTasks,
        private readonly int $daysActive,
        private readonly ?int $estimatedDaysRemaining
    ) {}

    public static function create(
        float $overall,
        array $stageProgress,
        int $tasksCompleted,
        int $totalTasks,
        int $daysActive,
        ?int $estimatedDaysRemaining = null
    ): self {
        return new self(
            min(100, max(0, $overall)),
            $stageProgress,
            $tasksCompleted,
            $totalTasks,
            $daysActive,
            $estimatedDaysRemaining
        );
    }

    public function overall(): float
    {
        return $this->overall;
    }

    public function stageProgress(): array
    {
        return $this->stageProgress;
    }

    public function tasksCompleted(): int
    {
        return $this->tasksCompleted;
    }

    public function totalTasks(): int
    {
        return $this->totalTasks;
    }

    public function daysActive(): int
    {
        return $this->daysActive;
    }

    public function estimatedDaysRemaining(): ?int
    {
        return $this->estimatedDaysRemaining;
    }

    public function isComplete(): bool
    {
        return $this->overall >= 100;
    }

    public function toArray(): array
    {
        return [
            'overall' => round($this->overall, 1),
            'stage_progress' => $this->stageProgress,
            'tasks_completed' => $this->tasksCompleted,
            'total_tasks' => $this->totalTasks,
            'days_active' => $this->daysActive,
            'estimated_days_remaining' => $this->estimatedDaysRemaining,
        ];
    }
}
