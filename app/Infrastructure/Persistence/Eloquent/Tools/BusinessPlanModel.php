<?php

namespace App\Infrastructure\Persistence\Eloquent\Tools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPlanModel extends Model
{
    protected $table = 'user_business_plans';

    protected $fillable = [
        'user_id',
        'business_name',
        'vision',
        'target_market',
        'income_goal_6months',
        'income_goal_1year',
        'team_size_goal',
        'marketing_strategy',
        'action_plan',
    ];

    protected $casts = [
        'income_goal_6months' => 'float',
        'income_goal_1year' => 'float',
        'team_size_goal' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
