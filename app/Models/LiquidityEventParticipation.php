<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidityEventParticipation extends Model
{
    use HasFactory;

    protected $fillable = [
        'liquidity_event_id',
        'investor_account_id',
        'status',
        'shares_offered',
        'shares_accepted',
        'amount_to_receive',
        'amount_received',
        'bank_details',
        'registered_at',
        'completed_at',
    ];

    protected $casts = [
        'shares_offered' => 'decimal:4',
        'shares_accepted' => 'decimal:4',
        'amount_to_receive' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'bank_details' => 'array',
        'registered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function liquidityEvent(): BelongsTo
    {
        return $this->belongsTo(LiquidityEvent::class);
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function register(float $sharesOffered): void
    {
        $this->update([
            'status' => 'registered',
            'shares_offered' => $sharesOffered,
            'registered_at' => now(),
        ]);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'interested' => 'Interested',
            'registered' => 'Registered',
            'approved' => 'Approved',
            'completed' => 'Completed',
            'withdrawn' => 'Withdrawn',
            default => ucfirst($this->status),
        };
    }
}
