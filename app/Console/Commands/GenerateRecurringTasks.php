<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\BMS\RecurringTaskModel;
use App\Domain\BMS\Operations\Services\OperationsService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'operations:generate-recurring-tasks';
    protected $description = 'Generate tasks from recurring task definitions';

    public function __construct(
        private OperationsService $operationsService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Generating recurring tasks...');

        $recurringTasks = RecurringTaskModel::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('next_generation_at')
                    ->orWhere('next_generation_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', today());
            })
            ->get();

        $generated = 0;

        foreach ($recurringTasks as $recurring) {
            try {
                $this->generateTask($recurring);
                $generated++;
                $this->info("Generated task from: {$recurring->title}");
            } catch (\Exception $e) {
                $this->error("Failed to generate task from {$recurring->title}: {$e->getMessage()}");
            }
        }

        $this->info("Generated {$generated} recurring tasks.");

        return Command::SUCCESS;
    }

    private function generateTask(RecurringTaskModel $recurring): void
    {
        // Create the task
        $taskData = [
            'title' => $recurring->title,
            'description' => $recurring->description,
            'type' => $recurring->type,
            'priority' => $recurring->priority,
            'workflow_id' => $recurring->workflow_id,
            'assigned_to' => $recurring->assigned_to,
            'recurring_task_id' => $recurring->id,
            'due_date' => $this->calculateDueDate($recurring),
        ];

        if ($recurring->template_id) {
            $taskData['template_id'] = $recurring->template_id;
        }

        $task = $this->operationsService->createTask($recurring->company_id, $taskData);

        // Update recurring task
        $recurring->last_generated_at = now();
        $recurring->next_generation_at = $this->calculateNextGeneration($recurring);
        $recurring->save();
    }

    private function calculateDueDate(RecurringTaskModel $recurring): Carbon
    {
        $now = now();

        return match ($recurring->recurrence_pattern) {
            'daily' => $now->addDays($recurring->recurrence_interval),
            'weekly' => $now->addWeeks($recurring->recurrence_interval),
            'monthly' => $now->addMonths($recurring->recurrence_interval),
            'quarterly' => $now->addMonths($recurring->recurrence_interval * 3),
            'yearly' => $now->addYears($recurring->recurrence_interval),
            default => $now->addDays(1),
        };
    }

    private function calculateNextGeneration(RecurringTaskModel $recurring): Carbon
    {
        $now = now();

        return match ($recurring->recurrence_pattern) {
            'daily' => $now->addDays($recurring->recurrence_interval),
            'weekly' => $now->addWeeks($recurring->recurrence_interval),
            'monthly' => $now->addMonths($recurring->recurrence_interval),
            'quarterly' => $now->addMonths($recurring->recurrence_interval * 3),
            'yearly' => $now->addYears($recurring->recurrence_interval),
            default => $now->addDays(1),
        };
    }
}
