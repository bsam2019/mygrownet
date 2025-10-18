<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'status',
        'payment_method',
        'payment_details',
        'reference',
        'external_reference',
        'payment_response',
        'failure_reason',
        'retry_count',
        'completed_at',
        'failed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'payment_response' => 'array',
        'retry_count' => 'integer',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public const TYPES = [
        'commission_payment' => 'Commission Payment',
        'subscription_payment' => 'Subscription Payment',
        'bonus_payment' => 'Bonus Payment',
        'withdrawal' => 'Withdrawal',
        'refund' => 'Refund'
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled'
    ];

    public const PAYMENT_METHODS = [
        'mobile_money' => 'Mobile Money',
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
        'wallet' => 'Wallet'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'payment_transaction_id');
    }

    /**
     * Scope for specific transaction types
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific statuses
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for transactions within date range
     */
    public function scopeWithinPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction can be retried
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && 
               $this->retry_count < 3 && 
               $this->created_at->diffInDays(now()) <= 7;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'K' . number_format($this->amount, 2);
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get commission count from payment details
     */
    public function getCommissionCountAttribute(): int
    {
        return $this->payment_details['commission_count'] ?? 0;
    }

    /**
     * Get phone number from payment details
     */
    public function getPhoneNumberAttribute(): ?string
    {
        return $this->payment_details['phone_number'] ?? null;
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted(string $externalReference = null, array $response = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'external_reference' => $externalReference,
            'payment_response' => $response
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(string $reason, array $response = []): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
            'payment_response' => $response
        ]);
    }

    /**
     * Increment retry count
     */
    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
    }

    /**
     * Get transaction statistics for a period
     */
    public static function getStatistics(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $transactions = self::where('created_at', '>=', $startDate)->get();

        return [
            'total_transactions' => $transactions->count(),
            'completed_transactions' => $transactions->where('status', 'completed')->count(),
            'failed_transactions' => $transactions->where('status', 'failed')->count(),
            'pending_transactions' => $transactions->where('status', 'pending')->count(),
            'total_amount' => $transactions->sum('amount'),
            'completed_amount' => $transactions->where('status', 'completed')->sum('amount'),
            'failed_amount' => $transactions->where('status', 'failed')->sum('amount'),
            'success_rate' => $transactions->count() > 0 
                ? ($transactions->where('status', 'completed')->count() / $transactions->count()) * 100 
                : 0,
            'average_amount' => $transactions->avg('amount'),
            'by_type' => $transactions->groupBy('type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                    'success_rate' => $group->count() > 0 
                        ? ($group->where('status', 'completed')->count() / $group->count()) * 100 
                        : 0
                ];
            })
        ];
    }
}