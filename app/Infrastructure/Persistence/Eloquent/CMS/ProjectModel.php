<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_projects';

    protected $fillable = [
        'company_id',
        'customer_id',
        'project_number',
        'name',
        'description',
        'site_location',
        'site_address',
        'latitude',
        'longitude',
        'status',
        'priority',
        'budget',
        'actual_cost',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'progress_percentage',
        'project_manager_id',
        'metadata',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'progress_percentage' => 'integer',
        'metadata' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'project_manager_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(JobModel::class, 'project_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestoneModel::class, 'project_id');
    }

    public function diaryEntries(): HasMany
    {
        return $this->hasMany(SiteDiaryEntryModel::class, 'project_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocumentModel::class, 'project_id');
    }

    public function billingStages(): HasMany
    {
        return $this->hasMany(BillingStageModel::class, 'project_id');
    }

    public function progressCertificates(): HasMany
    {
        return $this->hasMany(ProgressCertificateModel::class, 'project_id');
    }

    public function boqs(): HasMany
    {
        return $this->hasMany(BOQModel::class, 'project_id');
    }

    public function calculateProgress(): void
    {
        $totalMilestones = $this->milestones()->count();
        if ($totalMilestones === 0) {
            $this->progress_percentage = 0;
            return;
        }

        $completedMilestones = $this->milestones()->where('status', 'completed')->count();
        $this->progress_percentage = (int) (($completedMilestones / $totalMilestones) * 100);
    }

    public function isOverBudget(): bool
    {
        return $this->budget && $this->actual_cost > $this->budget;
    }

    public function isDelayed(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->gt($this->end_date) && $this->status !== 'completed';
    }
}
