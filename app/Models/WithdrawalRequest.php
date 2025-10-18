<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'investment_id',
        'amount',
        'fee',
        'penalty_amount',
        'net_amount',
        'type',
        'status',
        'payment_method',
        'wallet_address',
        'bank_details',
        'rejection_reason',
        'admin_notes',
        'transaction_id',
        'reference_number',
        'processed_by',
        'requested_at',
        'approved_at',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the user who made this withdrawal request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investment associated with this withdrawal
     */
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    /**
     * Get the user who processed this withdrawal
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get commission clawbacks related to this withdrawal
     */
    public function commissionClawbacks(): HasMany
    {
        return $this->hasMany(CommissionClawback::class);
    }
}
