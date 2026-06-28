<?php

namespace App\Domain\CMS\Projects\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ProjectMilestoneModel;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function generateProjectNumber(int $companyId): string
    {
        $year = date('Y');
        $lastProject = ProjectModel::where('company_id', $companyId)
            ->where('project_number', 'like', "PRJ-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastProject) {
            $lastNumber = (int) substr($lastProject->project_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PRJ-{$year}-{$newNumber}";
    }

    public function createProject(array $data): ProjectModel
    {
        return DB::transaction(function () use ($data) {
            $project = ProjectModel::create($data);

            // Create default milestones if provided
            if (!empty($data['milestones'])) {
                foreach ($data['milestones'] as $index => $milestone) {
                    ProjectMilestoneModel::create([
                        'project_id' => $project->id,
                        'name' => $milestone['name'],
                        'description' => $milestone['description'] ?? null,
                        'target_date' => $milestone['target_date'],
                        'order' => $index + 1,
                        'payment_percentage' => $milestone['payment_percentage'] ?? null,
                    ]);
                }
            }

            return $project->fresh(['milestones', 'customer', 'projectManager']);
        });
    }

    public function updateProject(ProjectModel $project, array $data): ProjectModel
    {
        $project->update($data);
        return $project->fresh(['milestones', 'customer', 'projectManager']);
    }

    public function updateProjectStatus(ProjectModel $project, string $status): ProjectModel
    {
        $project->status = $status;

        if ($status === 'active' && !$project->actual_start_date) {
            $project->actual_start_date = now();
        }

        if ($status === 'completed' && !$project->actual_end_date) {
            $project->actual_end_date = now();
            $project->progress_percentage = 100;
        }

        $project->save();
        return $project;
    }

    public function addMilestone(ProjectModel $project, array $data): ProjectMilestoneModel
    {
        $maxOrder = $project->milestones()->max('order') ?? 0;
        
        $milestone = ProjectMilestoneModel::create([
            'project_id' => $project->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'target_date' => $data['target_date'],
            'order' => $maxOrder + 1,
            'payment_percentage' => $data['payment_percentage'] ?? null,
        ]);

        $project->calculateProgress();
        $project->save();

        return $milestone;
    }

    public function completeMilestone(ProjectMilestoneModel $milestone): ProjectMilestoneModel
    {
        $milestone->status = 'completed';
        $milestone->actual_date = now();
        $milestone->save();

        $milestone->project->calculateProgress();
        $milestone->project->save();

        return $milestone;
    }

    public function getProjectStats(ProjectModel $project): array
    {
        return [
            'total_jobs' => $project->jobs()->count(),
            'completed_jobs' => $project->jobs()->where('status', 'completed')->count(),
            'total_milestones' => $project->milestones()->count(),
            'completed_milestones' => $project->milestones()->where('status', 'completed')->count(),
            'budget_used_percentage' => $project->budget > 0 
                ? ($project->actual_cost / $project->budget) * 100 
                : 0,
            'is_over_budget' => $project->isOverBudget(),
            'is_delayed' => $project->isDelayed(),
            'days_remaining' => $project->end_date 
                ? now()->diffInDays($project->end_date, false) 
                : null,
        ];
    }

    public function getProjectTimeline(ProjectModel $project): array
    {
        $milestones = $project->milestones()->orderBy('target_date')->get();
        $jobs = $project->jobs()->orderBy('created_at')->get();

        $timeline = [];

        foreach ($milestones as $milestone) {
            $timeline[] = [
                'type' => 'milestone',
                'date' => $milestone->target_date,
                'title' => $milestone->name,
                'status' => $milestone->status,
                'data' => $milestone,
            ];
        }

        foreach ($jobs as $job) {
            $timeline[] = [
                'type' => 'job',
                'date' => $job->created_at,
                'title' => $job->job_number,
                'status' => $job->status,
                'data' => $job,
            ];
        }

        usort($timeline, fn($a, $b) => $a['date'] <=> $b['date']);

        return $timeline;
    }
}
