<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;

class DeadLetterEvent extends Model
{
    protected $table = 'dead_letter_queue';

    protected $fillable = [
        'event_name',
        'payload',
        'error_message',
        'error_class',
        'queue',
        'status',
        'attempts',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'failed_at' => 'datetime',
        ];
    }
}
