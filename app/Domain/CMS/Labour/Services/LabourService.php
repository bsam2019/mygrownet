<?php

namespace App\Domain\CMS\Labour\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\CrewModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LabourTimesheetModel;
use Illuminate\Support\Facades\DB;

class LabourService
{
    public function createTimesheet(array $data): LabourTimesheetModel
    {
        return LabourTimesheetModel::create($data);
    }

    public function approveTimesheet(LabourTimesheetModel $timesheet, int $approvedBy): LabourTimesheetModel
    {
        $timesheet->status = 'approved';
        $timesheet->approved_by = $approvedBy;
        $timesheet->approved_at = now();
        $timesheet->save();

        return $timesheet;
    }

    public function getLabourCostForProject(int $projectId, $startDate = null, $endDate = null): array
    {
        $query = LabourTimesheetModel::where('project_id', $projectId)
            ->where('status', 'approved');

        if ($startDate) {
            $query->where('work_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('work_date', '<=', $endDate);
        }

        $timesheets = $query->get();

        return [
            'total_hours' => $timesheets->sum('regular_hours') + $timesheets->sum('overtime_hours'),
            'regular_hours' => $timesheets->sum('regular_hours'),
            'overtime_hours' => $timesheets->sum('overtime_hours'),
            'total_cost' => $timesheets->sum('total_cost'),
            'worker_count' => $timesheets->pluck('employee_id')->unique()->count(),
        ];
    }

    public function getLabourCostForJob(int $jobId, $startDate = null, $endDate = null): array
    {
        $query = LabourTimesheetModel::where('job_id', $jobId)
            ->where('status', 'approved');

        if ($startDate) {
            $query->where('work_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('work_date', '<=', $endDate);
        }

        $timesheets = $query->get();

        return [
            'total_hours' => $timesheets->sum('regular_hours') + $timesheets->sum('overtime_hours'),
            'regular_hours' => $timesheets->sum('regular_hours'),
            'overtime_hours' => $timesheets->sum('overtime_hours'),
            'total_cost' => $timesheets->sum('total_cost'),
            'worker_count' => $timesheets->pluck('employee_id')->unique()->count(),
        ];
    }

    public function getCrewProductivity(CrewModel $crew, $startDate, $endDate): array
    {
        $timesheets = LabourTimesheetModel::where('crew_id', $crew->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->get();

        $totalHours = $timesheets->sum('regular_hours') + $timesheets->sum('overtime_hours');
        $totalCost = $timesheets->sum('total_cost');
        $workDays = $timesheets->pluck('work_date')->unique()->count();

        return [
            'total_hours' => $totalHours,
            'total_cost' => $totalCost,
            'work_days' => $workDays,
            'average_hours_per_day' => $workDays > 0 ? $totalHours / $workDays : 0,
            'average_cost_per_day' => $workDays > 0 ? $totalCost / $workDays : 0,
        ];
    }
}
