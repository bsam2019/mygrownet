<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkloadSnapshotModel extends Model
{
    protected $table = 'cms_workload_snapshots';

    protected $fillable = [
        'company_id',
        'user_id',
        'snapshot_date',
        'active_tasks',
        'total_hours',
        'overdue_tasks',
        'completion_rate',
        'workload_score',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'active_tasks' => 'integer',
        'total_hours' => 'decimal:2',
        'overdue_tasks' => 'integer',
        'completion_rate' => 'decimal:2',
        'workload_score' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
