<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCompletionTrendModel extends Model
{
    protected $table = 'cms_task_completion_trends';

    protected $fillable = [
        'company_id',
        'trend_date',
        'period_type',
        'tasks_created',
        'tasks_completed',
        'tasks_overdue',
        'average_completion_time_hours',
        'completion_rate_percentage',
    ];

    protected $casts = [
        'trend_date' => 'date',
        'average_completion_time_hours' => 'decimal:2',
        'completion_rate_percentage' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }
}
