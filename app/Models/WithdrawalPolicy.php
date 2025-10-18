<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalPolicy extends Model
{
    protected $fillable = [
        'investment_id',
        'user_id',
        'withdrawal_type',
        'amount',
        'penalty_amount',
        'status',
        'approval_status',
        'processed_at',
        'approved_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}