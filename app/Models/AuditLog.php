<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    const EVENT_INVESTMENT_CREATED = 'investment_created';
    const EVENT_INVESTMENT_UPDATED = 'investment_updated';
    const EVENT_WITHDRAWAL_REQUESTED = 'withdrawal_requested';
    const EVENT_WITHDRAWAL_APPROVED = 'withdrawal_approved';
    const EVENT_WITHDRAWAL_REJECTED = 'withdrawal_rejected';
    const EVENT_COMMISSION_PAID = 'commission_paid';
    const EVENT_PROFIT_DISTRIBUTED = 'profit_distributed';
    const EVENT_TIER_UPGRADED = 'tier_upgraded';
    const EVENT_USER_BLOCKED = 'user_blocked';
    const EVENT_USER_UNBLOCKED = 'user_unblocked';
    const EVENT_LOGIN_ATTEMPT = 'login_attempt';
    const EVENT_PASSWORD_CHANGED = 'password_changed';

    protected $fillable = [
        'user_id',
        'event_type',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'amount',
        'transaction_reference',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for financial events
     */
    public function scopeFinancial($query)
    {
        return $query->whereNotNull('amount');
    }

    /**
     * Scope for specific event type
     */
    public function scopeOfType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for user activities
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Create an audit log entry
     */
    public static function logEvent(
        string $eventType,
        ?Model $auditable = null,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?float $amount = null,
        ?string $transactionReference = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'amount' => $amount,
            'transaction_reference' => $transactionReference,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Check if this is a financial transaction
     */
    public function isFinancialTransaction(): bool
    {
        return !is_null($this->amount);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return $this->amount ? 'K' . number_format($this->amount, 2) : '';
    }
}