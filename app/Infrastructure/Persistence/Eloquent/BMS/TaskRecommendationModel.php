<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskRecommendationModel extends Model
{
    protected $table = 'cms_task_recommendations';

    protected $fillable = [
        'company_id',
        'task_id',
        'recommendation_type',
        'priority',
        'message',
        'action_data',
        'is_dismissed',
        'dismissed_at',
        'dismissed_by',
    ];

    protected $casts = [
        'action_data' => 'array',
        'is_dismissed' => 'boolean',
        'dismissed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }

    public function dismissedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'dismissed_by');
    }
}
