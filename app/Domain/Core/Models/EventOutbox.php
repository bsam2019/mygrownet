<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;

class EventOutbox extends Model
{
    public $timestamps = false;

    protected $table = 'event_outbox';

    protected $fillable = [
        'event_name',
        'payload',
        'context',
        'publisher',
        'status',
        'attempts',
        'created_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'context' => 'array',
            'attempts' => 'integer',
            'created_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
