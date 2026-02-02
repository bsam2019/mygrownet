<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'share_type',
        'platform',
        'shared_url',
        'ip_address',
        'user_agent',
        'shared_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
    ];

    /**
     * Get the user who made the share
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get shares for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('shared_at', today());
    }

    /**
     * Scope: Get shares for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get shares by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('share_type', $type);
    }
}
