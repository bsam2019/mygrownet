<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewModel extends Model
{
    protected $table = 'cms_interviews';

    protected $fillable = [
        'application_id',
        'interview_type',
        'scheduled_date',
        'location',
        'meeting_link',
        'interviewer_ids',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'interviewer_ids' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplicationModel::class, 'application_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(InterviewEvaluationModel::class, 'interview_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
