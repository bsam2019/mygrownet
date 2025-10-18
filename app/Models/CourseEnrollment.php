<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'tier_at_enrollment',
        'progress_percentage',
        'completed_at',
        'certificate_issued_at',
        'status'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'certificate_issued_at' => 'datetime',
        'progress_percentage' => 'decimal:2'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // Business Logic
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && $this->completed_at !== null;
    }

    public function canReceiveCertificate(): bool
    {
        return $this->isCompleted() && 
               $this->course->certificate_eligible && 
               $this->certificate_issued_at === null;
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100.00
        ]);

        // Fire event for points system
        event(new \App\Events\CourseCompleted($this));
    }

    public function issueCertificate(): void
    {
        if ($this->canReceiveCertificate()) {
            $this->update(['certificate_issued_at' => now()]);
        }
    }
}