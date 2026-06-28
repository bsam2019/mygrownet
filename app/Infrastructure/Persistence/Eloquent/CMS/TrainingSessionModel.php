<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSessionModel extends Model
{
    protected $table = 'cms_training_sessions';

    protected $fillable = [
        'program_id',
        'session_name',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'venue',
        'trainer_id',
        'trainer_name',
        'available_seats',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgramModel::class, 'program_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'trainer_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollmentModel::class, 'session_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString())
            ->where('status', 'scheduled');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
