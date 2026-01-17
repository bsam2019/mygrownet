<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplacePayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'commission_deducted',
        'net_amount',
        'payout_method',
        'account_number',
        'account_name',
        'bank_name',
        'status',
        'reference',
        'seller_notes',
        'admin_notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
        'transaction_reference',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'commission_deducted' => 'integer',
        'net_amount' => 'integer',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the seller that owns the payout
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    /**
     * Get the admin who approved the payout
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the admin who processed the payout
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for pending payouts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved payouts
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for completed payouts
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if payout can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payout can be rejected
     */
    public function canBeRejected(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    /**
     * Check if payout can be processed
     */
    public function canBeProcessed(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'K' . number_format($this->amount / 100, 2);
    }

    /**
     * Get formatted net amount
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return 'K' . number_format($this->net_amount / 100, 2);
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
            'completed' => 'green',
            'rejected' => 'red',
            'failed' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }
}
