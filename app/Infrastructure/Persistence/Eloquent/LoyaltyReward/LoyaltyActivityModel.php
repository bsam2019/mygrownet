<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyActivityModel extends Model
{
    protected $table = 'loyalty_activities';

    protected $fillable = [
        'user_id',
        'cycle_id',
        'activity_type',
        'description',
        'performed_at',
        'verified',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(LoyaltyGrowthCycleModel::class, 'cycle_id');
    }
}
