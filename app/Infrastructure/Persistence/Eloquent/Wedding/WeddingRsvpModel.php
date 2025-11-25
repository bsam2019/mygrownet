<?php

namespace App\Infrastructure\Persistence\Eloquent\Wedding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingRsvpModel extends Model
{
    protected $table = 'wedding_rsvps';

    protected $fillable = [
        'wedding_event_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'attending',
        'guest_count',
        'dietary_restrictions',
        'message',
        'submitted_at',
    ];

    protected $casts = [
        'attending' => 'boolean',
        'guest_count' => 'integer',
        'submitted_at' => 'datetime',
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
