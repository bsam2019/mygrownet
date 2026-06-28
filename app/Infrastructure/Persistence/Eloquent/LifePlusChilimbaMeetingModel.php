<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusChilimbaMeetingModel extends Model
{
    protected $table = 'lifeplus_chilimba_meetings';

    protected $fillable = [
        'group_id',
        'created_by',
        'meeting_date',
        'attendees',
        'total_collected',
        'payout_given_to',
        'loans_approved',
        'decisions',
        'next_meeting_date',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'next_meeting_date' => 'date',
        'total_collected' => 'decimal:2',
        'attendees' => 'array',
        'loans_approved' => 'array',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaGroupModel::class, 'group_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
