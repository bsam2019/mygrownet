<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabourTimesheetModel extends Model
{
    protected $table = 'cms_labour_timesheets';

    protected $fillable = [
        'employee_id', 'project_id', 'job_id', 'crew_id', 'work_date',
        'start_time', 'end_time', 'regular_hours', 'overtime_hours',
        'hourly_rate', 'total_cost', 'work_type', 'work_description',
        'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($timesheet) {
            $regularCost = $timesheet->regular_hours * $timesheet->hourly_rate;
            $overtimeCost = $timesheet->overtime_hours * $timesheet->hourly_rate * 1.5;
            $timesheet->total_cost = $regularCost + $overtimeCost;
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(CrewModel::class, 'crew_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'approved_by');
    }
}
