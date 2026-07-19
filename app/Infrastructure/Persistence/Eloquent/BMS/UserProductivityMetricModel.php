<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProductivityMetricModel extends Model
{
    protected $table = 'cms_user_productivity_metrics';

    protected $fillable = [
        'company_id',
        'user_id',
        'metric_date',
        'tasks_completed',
        'tasks_started',
        'hours_logged',
        'estimated_hours',
        'efficiency_percentage',
        'on_time_completions',
        'late_completions',
        'average_task_duration_hours',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'hours_logged' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'efficiency_percentage' => 'decimal:2',
        'average_task_duration_hours' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
