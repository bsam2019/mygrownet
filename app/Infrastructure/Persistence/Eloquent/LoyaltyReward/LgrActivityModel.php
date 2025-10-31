<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class LgrActivityModel extends Model
{
    protected $table = 'lgr_activities';

    protected $fillable = [
        'user_id',
        'lgr_cycle_id',
        'activity_date',
        'activity_type',
        'activity_description',
        'activity_metadata',
        'lgc_earned',
        'verified',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'activity_metadata' => 'array',
        'lgc_earned' => 'decimal:2',
        'verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(LgrCycleModel::class, 'lgr_cycle_id');
    }
}
