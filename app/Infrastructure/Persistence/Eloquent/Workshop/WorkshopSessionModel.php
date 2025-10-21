<?php

namespace App\Infrastructure\Persistence\Eloquent\Workshop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopSessionModel extends Model
{
    protected $table = 'workshop_sessions';

    protected $fillable = [
        'workshop_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'meeting_link',
        'duration_minutes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(WorkshopModel::class, 'workshop_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(WorkshopAttendanceModel::class, 'workshop_session_id');
    }
}
