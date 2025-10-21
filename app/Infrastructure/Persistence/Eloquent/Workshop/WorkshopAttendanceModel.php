<?php

namespace App\Infrastructure\Persistence\Eloquent\Workshop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class WorkshopAttendanceModel extends Model
{
    protected $table = 'workshop_attendance';

    protected $fillable = [
        'workshop_session_id',
        'user_id',
        'checked_in_at',
        'checked_in_by',
        'notes',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkshopSessionModel::class, 'workshop_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
