<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_tasks';

    protected $fillable = [
        'company_id',
        'task_number',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'workflow_id',
        'workflow_stage_id',
        'template_id',
        'recurring_task_id',
        'tags',
        'progress_percentage',
        'assigned_to',
        'created_by',
        'project_id',
        'job_id',
        'production_order_id',
        'installation_schedule_id',
        'due_date',
        'started_at',
        'completed_at',
        'last_activity_at',
        'estimated_hours',
        'actual_hours',
    ];

    protected $casts = [
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'tags' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(WorkflowModel::class, 'workflow_id');
    }

    public function workflowStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStageModel::class, 'workflow_stage_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderModel::class, 'production_order_id');
    }

    public function installationSchedule(): BelongsTo
    {
        return $this->belongsTo(InstallationScheduleModel::class, 'installation_schedule_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskLogModel::class, 'task_id')->latest();
    }

    public function issues(): HasMany
    {
        return $this->hasMany(TaskIssueModel::class, 'task_id');
    }

    public function checklistResponses(): HasMany
    {
        return $this->hasMany(TaskChecklistResponseModel::class, 'task_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(TaskRecommendationModel::class, 'task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskCommentModel::class, 'task_id')->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachmentModel::class, 'task_id');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependencyModel::class, 'task_id');
    }

    public function dependentTasks(): HasMany
    {
        return $this->hasMany(TaskDependencyModel::class, 'depends_on_task_id');
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(TaskWatcherModel::class, 'task_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TaskTimeEntryModel::class, 'task_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplateModel::class, 'template_id');
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTaskModel::class, 'recurring_task_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress', 'blocked']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // Helper methods
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    public function canMoveToNextStage(): bool
    {
        if (!$this->workflow_stage_id) {
            return false;
        }

        $stage = $this->workflowStage;
        
        // Check if approval is required
        if ($stage->requires_approval) {
            // Add approval logic here
            return false;
        }

        return true;
    }

    public function moveToNextStage(): bool
    {
        if (!$this->canMoveToNextStage()) {
            return false;
        }

        $nextStage = $this->workflowStage->getNextStage();
        
        if (!$nextStage) {
            return false;
        }

        $this->workflow_stage_id = $nextStage->id;
        $this->save();

        // Log the stage change
        $this->logs()->create([
            'user_id' => auth()->id(),
            'action' => 'stage_changed',
            'note' => "Moved to stage: {$nextStage->name}",
            'changes' => [
                'from_stage' => $this->workflowStage->name,
                'to_stage' => $nextStage->name,
            ],
        ]);

        return true;
    }
}
