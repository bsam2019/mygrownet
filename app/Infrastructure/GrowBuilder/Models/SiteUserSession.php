<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SiteUserSession extends Model
{
    protected $table = 'site_user_sessions';

    public $timestamps = false;

    protected $fillable = [
        'site_user_id',
        'token',
        'ip_address',
        'user_agent',
        'last_activity_at',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(SiteUser::class, 'site_user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function touch($attribute = null): bool
    {
        if ($attribute) {
            return parent::touch($attribute);
        }
        
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Update the last activity timestamp
     */
    public function updateActivity(): bool
    {
        $this->last_activity_at = now();
        return $this->save();
    }

    public static function createForUser(
        SiteUser $user,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        int $expiresInMinutes = 10080 // 7 days
    ): self {
        return static::create([
            'site_user_id' => $user->id,
            'token' => Str::random(64),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'created_at' => now(),
        ]);
    }

    public static function cleanupExpired(): int
    {
        return static::expired()->delete();
    }
}
