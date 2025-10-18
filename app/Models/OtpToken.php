<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class OtpToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'type',
        'purpose',
        'identifier',
        'expires_at',
        'verified_at',
        'is_used',
        'attempts',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_used' => 'boolean',
        'metadata' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired() && $this->attempts < 3;
    }

    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'verified_at' => now()
        ]);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function scopeValid($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now())
                    ->where('attempts', '<', 3);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPurpose($query, $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeForType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $minutes = 5)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}