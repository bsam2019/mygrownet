<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorPollVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'investor_account_id',
        'selected_options',
        'voted_at',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'voted_at' => 'datetime',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(InvestorPoll::class, 'poll_id');
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }
}
