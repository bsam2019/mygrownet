<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;

class RewardPoolModel extends Model
{
    protected $table = 'reward_pools';

    protected $fillable = [
        'total_balance',
        'available_balance',
        'reserved_balance',
        'last_updated',
    ];

    protected $casts = [
        'total_balance' => 'integer',
        'available_balance' => 'integer',
        'reserved_balance' => 'integer',
        'last_updated' => 'datetime',
    ];
}
