<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareholderMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_round_id',
        'title',
        'description',
        'meeting_type',
        'scheduled_at',
        'location',
        'virtual_link',
        'agenda',
        'minutes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'agenda' => 'array',
    ];

    public function investmentRound(): BelongsTo
    {
        return $this->belongsTo(InvestmentRound::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    public function isUpcoming(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at->isFuture();
    }

    public function isPast(): bool
    {
        return $this->status === 'completed' || $this->scheduled_at->isPast();
    }
}
