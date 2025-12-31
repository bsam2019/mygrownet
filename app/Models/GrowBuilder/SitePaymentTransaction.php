<?php

namespace App\Models\GrowBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePaymentTransaction extends Model
{
    protected $table = 'growbuilder_site_payment_transactions';

    protected $fillable = [
        'site_id',
        'payment_config_id',
        'transaction_reference',
        'external_reference',
        'amount',
        'currency',
        'phone_number',
        'customer_name',
        'customer_email',
        'description',
        'status',
        'metadata',
        'raw_response',
        'refund_reference',
        'refund_amount',
        'refund_reason',
        'verified_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'metadata' => 'array',
        'raw_response' => 'array',
        'verified_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the site that owns this transaction
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(\App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::class, 'site_id');
    }

    /**
     * Get the payment config used for this transaction
     */
    public function paymentConfig(): BelongsTo
    {
        return $this->belongsTo(SitePaymentConfig::class, 'payment_config_id');
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if transaction is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Scope to get completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get pending transactions
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    /**
     * Scope to get failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
