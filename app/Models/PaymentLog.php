<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_type',
        'amount',
        'currency',
        'payment_method',
        'provider',
        'provider_reference',
        'internal_reference',
        'status',
        'reconciled',
        'reconciled_at',
        'reconciled_by',
        'transaction_id',
        'provider_data',
        'notes',
        'ip_address',
        'user_agent',
        'initiated_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'provider_data' => 'array',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['initiated', 'pending', 'processing']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('reconciled', false)
            ->where('status', 'completed');
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Helper methods
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'notes' => $reason,
        ]);
    }

    public function markAsReconciled(int $reconciledBy): void
    {
        $this->update([
            'reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => $reconciledBy,
            'status' => 'reconciled',
        ]);
    }

    public function linkTransaction(int $transactionId): void
    {
        $this->update(['transaction_id' => $transactionId]);
    }

    /**
     * Generate internal reference
     */
    public static function generateReference(string $prefix = 'PAY'): string
    {
        return $prefix . '-' . strtoupper(uniqid()) . '-' . time();
    }
}
