<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentUsageModel extends Model
{
    protected $table = 'cms_equipment_usage';

    protected $fillable = [
        'equipment_id', 'project_id', 'job_id', 'assigned_to', 'start_date',
        'end_date', 'start_time', 'end_time', 'hours_used', 'fuel_cost', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hours_used' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'assigned_to');
    }
}
