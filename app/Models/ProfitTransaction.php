<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfitTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'investment_id',
        'amount',
        'type',
        'description',
        'status',
        'processed_at',
        'reference_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Transaction types
     */
    const TYPE_MONTHLY_PROFIT = 'monthly_profit';
    const TYPE_QUARTERLY_SHARE = 'quarterly_share';
    const TYPE_REFERRAL_COMMISSION = 'referral_commission';
    const TYPE_PERFORMANCE_BONUS = 'performance_bonus';
    const TYPE_WITHDRAWAL_PENALTY = 'withdrawal_penalty';

    /**
     * Transaction statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investment associated with the transaction.
     */
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    /**
     * Scope a query to only include transactions of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include transactions with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include transactions within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include processed transactions.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Get the formatted amount with currency symbol.
     */
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Mark the transaction as processed.
     */
    public function markAsProcessed()
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now()
        ]);
    }

    /**
     * Mark the transaction as failed.
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => self::STATUS_FAILED
        ]);
    }

    /**
     * Mark the transaction as cancelled.
     */
    public function markAsCancelled()
    {
        $this->update([
            'status' => self::STATUS_CANCELLED
        ]);
    }
} 