<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class JobPostingModel extends Model
{
    use HasFactory;

    protected $table = 'job_postings';

    protected $fillable = [
        'position_id',
        'department_id',
        'title',
        'description',
        'requirements',
        'benefits',
        'salary_min',
        'salary_max',
        'employment_type',
        'location',
        'is_active',
        'posted_at',
        'expires_at',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'is_active' => 'boolean',
        'posted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the position this job posting is for
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(PositionModel::class, 'position_id');
    }

    /**
     * Get the department this job posting belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    /**
     * Get all applications for this job posting
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplicationModel::class, 'job_posting_id');
    }

    /**
     * Scope to get only active job postings
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to get expired job postings
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if the job posting is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get formatted salary range
     */
    public function getSalaryRangeAttribute(): string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return 'Competitive';
        }

        if ($this->salary_min && $this->salary_max) {
            return "K{$this->salary_min} - K{$this->salary_max}";
        }

        if ($this->salary_min) {
            return "From K{$this->salary_min}";
        }

        return "Up to K{$this->salary_max}";
    }
}
