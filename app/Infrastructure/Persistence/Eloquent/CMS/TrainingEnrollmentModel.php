<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingEnrollmentModel extends Model
{
    protected $table = 'cms_training_enrollments';

    protected $fillable = [
        'session_id',
        'worker_id',
        'enrolled_date',
        'status',
        'attendance_percentage',
        'assessment_score',
        'pass_status',
        'completion_date',
        'feedback',
        'certificate_issued',
        'certificate_number',
        'enrolled_by',
    ];

    protected $casts = [
        'enrolled_date' => 'date',
        'completion_date' => 'date',
        'assessment_score' => 'decimal:2',
        'certificate_issued' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSessionModel::class, 'session_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'enrolled_by');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(TrainingAttendanceModel::class, 'enrollment_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePassed($query)
    {
        return $query->where('pass_status', 'passed');
    }
}
