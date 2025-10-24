<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_type',
        'level_achieved',
        'status',
        'description',
        'earned_at',
        'approved_at',
        'delivered_at',
        'approved_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'approved_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the physical reward
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved the reward
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get formatted reward name
     */
    public function getRewardNameAttribute(): string
    {
        return match ($this->reward_type) {
            'smartphone' => 'Smartphone',
            'motorbike' => 'Motorbike',
            'vehicle' => 'Vehicle',
            'luxury_vehicle' => 'Luxury Vehicle',
            'investment_property' => 'Investment Property',
            default => ucfirst(str_replace('_', ' ', $this->reward_type)),
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'approved' => 'blue',
            'processing' => 'indigo',
            'delivered' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope for pending rewards
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved rewards
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for delivered rewards
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Approve the reward
     */
    public function approve(int $approvedBy): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => 'processing',
        ]);
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(): bool
    {
        return $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Cancel the reward
     */
    public function cancel(string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);
    }
}
