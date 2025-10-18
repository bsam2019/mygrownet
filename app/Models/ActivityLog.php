<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'loggable_type',
        'loggable_id',
        'old_values',
        'new_values',
        'properties',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'properties' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
