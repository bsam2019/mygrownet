<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class LgrQualificationModel extends Model
{
    protected $table = 'lgr_qualifications';

    protected $fillable = [
        'user_id',
        'starter_package_completed',
        'starter_package_completed_at',
        'training_completed',
        'training_completed_at',
        'first_level_members',
        'network_requirement_met',
        'network_requirement_met_at',
        'activities_completed',
        'activity_requirement_met',
        'activity_requirement_met_at',
        'fully_qualified',
        'fully_qualified_at',
    ];

    protected $casts = [
        'starter_package_completed' => 'boolean',
        'starter_package_completed_at' => 'datetime',
        'training_completed' => 'boolean',
        'training_completed_at' => 'datetime',
        'first_level_members' => 'integer',
        'network_requirement_met' => 'boolean',
        'network_requirement_met_at' => 'datetime',
        'activities_completed' => 'integer',
        'activity_requirement_met' => 'boolean',
        'activity_requirement_met_at' => 'datetime',
        'fully_qualified' => 'boolean',
        'fully_qualified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
