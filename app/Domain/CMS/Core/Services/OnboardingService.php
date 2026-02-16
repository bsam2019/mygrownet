<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\OnboardingTemplateModel;
use App\Infrastructure\Persistence\Eloquent\CMS\OnboardingTaskModel;
use App\Infrastructure\Persistence\Eloquent\CMS\EmployeeOnboardingModel;
use App\Infrastructure\Persistence\Eloquent\CMS\OnboardingTaskProgressModel;
use Carbon\Carbon;

class OnboardingService
{
    public function createTemplate(int $companyId, array $data): OnboardingTemplateModel
    {
        return OnboardingTemplateModel::create([
            'company_id' => $companyId,
            'template_name' => $data['template_name'],
            'department_id' => $data['department_id'] ?? null,
            'is_default' => $data['is_default'] ?? false,
        ]);
    }

    public function addTaskToTemplate(int $templateId, array $data): OnboardingTaskModel
    {
        return OnboardingTaskModel::create([
            'template_id' => $templateId,
            'task_name' => $data['task_name'],
            'description' => $data['description'] ?? null,
            'task_category' => $data['task_category'],
            'assigned_to_role' => $data['assigned_to_role'],
            'due_days_after_start' => $data['due_days_after_start'],
            'is_mandatory' => $data['is_mandatory'] ?? true,
            'display_order' => $data['display_order'] ?? 0,
        ]);
    }

    public function startOnboarding(int $workerId, int $templateId, Carbon $startDate): EmployeeOnboardingModel
    {
        $template = OnboardingTemplateModel::with('tasks')->findOrFail($templateId);
        
        $maxDays = $template->tasks->max('due_days_after_start') ?? 30;
        $expectedCompletionDate = $startDate->copy()->addDays($maxDays);

        $onboarding = EmployeeOnboardingModel::create([
            'worker_id' => $workerId,
            'template_id' => $templateId,
            'start_date' => $startDate,
            'expected_completion_date' => $expectedCompletionDate,
            'status' => 'in_progress',
            'completion_percentage' => 0,
        ]);

        foreach ($template->tasks as $task) {
            OnboardingTaskProgressModel::create([
                'onboarding_id' => $onboarding->id,
                'task_id' => $task->id,
                'due_date' => $startDate->copy()->addDays($task->due_days_after_start),
                'is_completed' => false,
            ]);
        }

        return $onboarding->load('taskProgress.task');
    }

    public function completeTask(int $taskProgressId, ?int $userId = null, ?string $notes = null): OnboardingTaskProgressModel
    {
        $taskProgress = OnboardingTaskProgressModel::findOrFail($taskProgressId);
        
        $taskProgress->update([
            'is_completed' => true,
            'completed_date' => now(),
            'notes' => $notes,
        ]);

        $this->updateOnboardingProgress($taskProgress->onboarding_id);

        return $taskProgress;
    }

    public function updateOnboardingProgress(int $onboardingId): void
    {
        $onboarding = EmployeeOnboardingModel::with('taskProgress')->findOrFail($onboardingId);
        
        $totalTasks = $onboarding->taskProgress->count();
        $completedTasks = $onboarding->taskProgress->where('is_completed', true)->count();
        
        $percentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        
        $status = 'in_progress';
        $actualCompletionDate = null;
        
        if ($percentage == 100) {
            $status = 'completed';
            $actualCompletionDate = now();
        }

        $onboarding->update([
            'completion_percentage' => $percentage,
            'status' => $status,
            'actual_completion_date' => $actualCompletionDate,
        ]);
    }

    public function getOnboardingProgress(int $workerId)
    {
        return EmployeeOnboardingModel::where('worker_id', $workerId)
            ->with(['template', 'taskProgress.task'])
            ->first();
    }

    public function getOverdueTasks(int $companyId)
    {
        return OnboardingTaskProgressModel::whereHas('onboarding.worker', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['onboarding.worker', 'task'])
            ->overdue()
            ->get();
    }
}
