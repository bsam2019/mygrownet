<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubcontractorAssignmentModel extends Model
{
    protected $table = 'cms_subcontractor_assignments';

    protected $fillable = [
        'subcontractor_id',
        'project_id',
        'job_id',
        'work_description',
        'quoted_amount',
        'actual_amount',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(SubcontractorModel::class, 'subcontractor_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubcontractorPaymentModel::class, 'assignment_id');
    }

    public function rating(): HasMany
    {
        return $this->hasMany(SubcontractorRatingModel::class, 'assignment_id');
    }

    public function isDelayed(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }
        return $this->end_date && now()->gt($this->end_date);
    }
}
