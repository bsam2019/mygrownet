<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareholderVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'resolution_id',
        'investor_account_id',
        'vote',
        'voting_power',
        'selected_option',
        'ip_address',
        'voted_at',
    ];

    protected $casts = [
        'voting_power' => 'decimal:4',
        'voted_at' => 'datetime',
    ];

    public function resolution(): BelongsTo
    {
        return $this->belongsTo(ShareholderResolution::class, 'resolution_id');
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }
}
