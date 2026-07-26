<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;

class EventInbox extends Model
{
    public $timestamps = false;

    protected $table = 'event_inbox';

    protected $fillable = [
        'event_id',
        'event_name',
        'payload',
        'publisher',
        'status',
        'received_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
