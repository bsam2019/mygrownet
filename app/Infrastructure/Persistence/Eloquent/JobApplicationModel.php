<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class JobApplicationModel extends Model
{
    use HasFactory;

    protected $table = 'job_applications';

    protected $fillable = [
        'job_posting_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'national_id',
        'address',
        'resume_path',
        'cover_letter',
        'expected_salary',
        'status',
        'admin_notes',
        'reviewed_at',
        'reviewed_by',
        'applied_at',
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    /**
     * Get the job posting this application is for
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPostingModel::class, 'job_posting_id');
    }

    /**
     * Get the user who reviewed this application
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    /**
     * Scope to get applications by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending applications
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope to get under review applications
     */
    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('status', 'under_review');
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get resume URL if exists
     */
    public function getResumeUrlAttribute(): ?string
    {
        return $this->resume_path ? Storage::url($this->resume_path) : null;
    }

    /**
     * Check if application has a resume
     */
    public function hasResume(): bool
    {
        return !empty($this->resume_path) && Storage::exists($this->resume_path);
    }

    /**
     * Mark application as under review
     */
    public function markAsUnderReview(int $reviewerId): void
    {
        $this->update([
            'status' => 'under_review',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
        ]);
    }

    /**
     * Mark application as hired
     */
    public function markAsHired(int $reviewerId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'hired',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Mark application as rejected
     */
    public function markAsRejected(int $reviewerId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'bg-blue-100 text-blue-800',
            'under_review' => 'bg-yellow-100 text-yellow-800',
            'interviewed' => 'bg-purple-100 text-purple-800',
            'hired' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
