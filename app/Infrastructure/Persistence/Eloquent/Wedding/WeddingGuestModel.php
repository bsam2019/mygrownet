<?php

namespace App\Infrastructure\Persistence\Eloquent\Wedding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingGuestModel extends Model
{
    protected $table = 'wedding_guests';

    protected $fillable = [
        'wedding_event_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'allowed_guests',
        'group_name',
        'notes',
        'invitation_sent',
        'invitation_sent_at',
        'rsvp_status',
        'confirmed_guests',
        'dietary_restrictions',
        'rsvp_message',
        'rsvp_submitted_at',
    ];

    protected $casts = [
        'allowed_guests' => 'integer',
        'confirmed_guests' => 'integer',
        'invitation_sent' => 'boolean',
        'invitation_sent_at' => 'datetime',
        'rsvp_submitted_at' => 'datetime',
    ];

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEventModel::class, 'wedding_event_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
