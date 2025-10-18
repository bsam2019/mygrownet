<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionClawback extends Model
{
    protected $fillable = [
        'referral_commission_id',
        'user_id',
        'withdrawal_request_id',
        'original_amount',
        'clawback_amount',
        'clawback_percentage',
        'reason',
        'status',
        'notes',
        'processed_by',
        'processed_at'
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'clawback_amount' => 'decimal:2',
        'clawback_percentage' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the referral commission that is being clawed back
     */
    public function referralCommission(): BelongsTo
    {
        return $this->belongsTo(ReferralCommission::class);
    }

    /**
     * Get the user whose commission is being clawed back
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the withdrawal request that triggered this clawback
     */
    public function withdrawalRequest(): BelongsTo
    {
        return $this->belongsTo(WithdrawalRequest::class);
    }

    /**
     * Get the user who processed this clawback
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
