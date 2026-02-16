<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\LeaveBalanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveRequestModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveTypeModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveManagementService
{
    public function createLeaveRequest(array $data): LeaveRequestModel
    {
        return DB::transaction(function () use ($data) {
            // Generate leave request number
            $data['leave_request_number'] = $this->generateLeaveRequestNumber($data['company_id']);
            
            // Calculate total days
            $data['total_days'] = $this->calculateLeaveDays(
                $data['start_date'],
                $data['end_date'],
                $data['company_id']
            );
            
            $leaveRequest = LeaveRequestModel::create($data);
            
            // Update pending days in balance
            $this->updatePendingDays($leaveRequest);
            
            return $leaveRequest;
        });
    }

    public function approveLeaveRequest(int $leaveRequestId, int $approvedBy, ?string $notes = null): LeaveRequestModel
    {
        return DB::transaction(function () use ($leaveRequestId, $approvedBy, $notes) {
            $leaveRequest = LeaveRequestModel::findOrFail($leaveRequestId);
            
            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);
            
            // Update leave balance
            $this->deductLeaveBalance($leaveRequest);
            
            return $leaveRequest->fresh();
        });
    }

    public function rejectLeaveRequest(int $leaveRequestId, int $rejectedBy, string $reason): LeaveRequestModel
    {
        return DB::transaction(function () use ($leaveRequestId, $rejectedBy, $reason) {
            $leaveRequest = LeaveRequestModel::findOrFail($leaveRequestId);
            
            $leaveRequest->update([
                'status' => 'rejected',
                'rejected_by' => $rejectedBy,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);
            
            // Restore pending days
            $this->restorePendingDays($leaveRequest);
            
            return $leaveRequest->fresh();
        });
    }

    public function initializeLeaveBalances(int $workerId, int $year): void
    {
        $worker = WorkerModel::findOrFail($workerId);
        $leaveTypes = LeaveTypeModel::where('company_id', $worker->company_id)
            ->where('is_active', true)
            ->get();
        
        foreach ($leaveTypes as $leaveType) {
            LeaveBalanceModel::updateOrCreate(
                [
                    'worker_id' => $workerId,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ],
                [
                    'company_id' => $worker->company_id,
                    'total_days' => $leaveType->default_days_per_year,
                    'used_days' => 0,
                    'pending_days' => 0,
                    'available_days' => $leaveType->default_days_per_year,
                    'carried_forward_days' => 0,
                ]
            );
        }
    }

    public function getLeaveBalance(int $workerId, int $leaveTypeId, int $year): ?LeaveBalanceModel
    {
        return LeaveBalanceModel::where('worker_id', $workerId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();
    }

    protected function generateLeaveRequestNumber(int $companyId): string
    {
        $year = date('Y');
        $lastRequest = LeaveRequestModel::where('company_id', $companyId)
            ->where('leave_request_number', 'like', "LVE-$year-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastRequest) {
            $lastNumber = (int) substr($lastRequest->leave_request_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "LVE-$year-$newNumber";
    }

    protected function calculateLeaveDays(string $startDate, string $endDate, int $companyId): float
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = 0;
        
        // Get public holidays
        $holidays = DB::table('cms_public_holidays')
            ->where('company_id', $companyId)
            ->whereBetween('holiday_date', [$startDate, $endDate])
            ->pluck('holiday_date')
            ->toArray();
        
        while ($start->lte($end)) {
            // Skip weekends and public holidays
            if (!$start->isWeekend() && !in_array($start->format('Y-m-d'), $holidays)) {
                $days++;
            }
            $start->addDay();
        }
        
        return $days;
    }

    protected function updatePendingDays(LeaveRequestModel $leaveRequest): void
    {
        $balance = $this->getLeaveBalance(
            $leaveRequest->worker_id,
            $leaveRequest->leave_type_id,
            date('Y')
        );
        
        if ($balance) {
            $balance->pending_days += $leaveRequest->total_days;
            $balance->available_days = $balance->total_days - $balance->used_days - $balance->pending_days;
            $balance->save();
        }
    }

    protected function deductLeaveBalance(LeaveRequestModel $leaveRequest): void
    {
        $balance = $this->getLeaveBalance(
            $leaveRequest->worker_id,
            $leaveRequest->leave_type_id,
            date('Y')
        );
        
        if ($balance) {
            $balance->pending_days -= $leaveRequest->total_days;
            $balance->used_days += $leaveRequest->total_days;
            $balance->available_days = $balance->total_days - $balance->used_days - $balance->pending_days;
            $balance->save();
        }
    }

    protected function restorePendingDays(LeaveRequestModel $leaveRequest): void
    {
        $balance = $this->getLeaveBalance(
            $leaveRequest->worker_id,
            $leaveRequest->leave_type_id,
            date('Y')
        );
        
        if ($balance) {
            $balance->pending_days -= $leaveRequest->total_days;
            $balance->available_days = $balance->total_days - $balance->used_days - $balance->pending_days;
            $balance->save();
        }
    }
}
