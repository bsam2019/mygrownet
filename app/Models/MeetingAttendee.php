<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'shareholder_meeting_id',
        'user_id',
        'rsvp_status',
        'attended',
        'proxy_holder_id',
        'notes',
    ];

    protected $casts = [
        'attended' => 'boolean',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(ShareholderMeeting::class, 'shareholder_meeting_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proxyHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proxy_holder_id');
    }

    public function hasProxy(): bool
    {
        return !is_null($this->proxy_holder_id);
    }
}
