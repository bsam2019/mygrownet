<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Withdrawal extends Model
{
    use HasFactory, HasActivityLogs;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'withdrawal_method',
        'wallet_address',
        'reason',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }
    
    /**
     * Scope to exclude starter kit wallet payments
     * These are internal transactions that should appear in transactions, not withdrawals
     */
    public function scopeExcludeStarterKit($query)
    {
        return $query->where(function($q) {
            $q->where('withdrawal_method', '!=', 'wallet_payment')
              ->orWhereNull('withdrawal_method')
              ->orWhere(function($q2) {
                  $q2->where('withdrawal_method', 'wallet_payment')
                     ->where('reason', 'NOT LIKE', '%Starter Kit%')
                     ->where('reason', 'NOT LIKE', '%starter kit%');
              });
        });
    }
    
    /**
     * Scope for actual withdrawals only (money leaving the system)
     */
    public function scopeActualWithdrawals($query)
    {
        return $query->excludeStarterKit();
    }
}
