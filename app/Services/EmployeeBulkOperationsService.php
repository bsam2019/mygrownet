<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmployeeBulkOperationsService
{
    public function bulkUpdateStatus(array $employeeIds, string $newStatus, string $reason = ''): array
    {
        $validator = Validator::make([
            'employee_ids' => $employeeIds,
            'status' => $newStatus,
        ], [
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'status' => 'required|in:active,inactive,terminated,suspended',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($employeeIds),
        ];

        DB::beginTransaction();
        try {
            foreach ($employeeIds as $employeeId) {
                try {
                    $employee = EmployeeModel::findOrFail($employeeId);
                    
                    // Validate status transition
                    if (!$this->canTransitionStatus($employee->employment_status, $newStatus)) {
                        $results['failed'][] = [
                            'id' => $employeeId,
                            'name' => $employee->full_name,
                            'error' => "Cannot transition from {$employee->employment_status} to {$newStatus}",
                        ];
                        continue;
                    }

                    $oldStatus = $employee->employment_status;
                    $employee->employment_status = $newStatus;
                    
                    if ($newStatus === 'terminated') {
                        $employee->termination_date = now();
                    }

                    // Add note about bulk operation
                    $note = "Bulk status change from {$oldStatus} to {$newStatus}";
                    if ($reason) {
                        $note .= ". Reason: {$reason}";
                    }
                    $employee->notes = ($employee->notes ?? '') . "\n[" . now()->format('Y-m-d H:i:s') . "] {$note}";
                    
                    $employee->save();

                    // Handle user account status
                    if ($employee->user) {
                        $this->updateUserAccountStatus($employee->user, $newStatus);
                    }

                    $results['success'][] = [
                        'id' => $employeeId,
                        'name' => $employee->full_name,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ];

                    Log::info('Bulk status update successful', [
                        'employee_id' => $employeeId,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'reason' => $reason,
                    ]);

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'id' => $employeeId,
                        'name' => $employee->full_name ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function bulkTransferDepartment(array $employeeIds, int $newDepartmentId, ?int $newPositionId = null): array
    {
        $validator = Validator::make([
            'employee_ids' => $employeeIds,
            'department_id' => $newDepartmentId,
            'position_id' => $newPositionId,
        ], [
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $department = DepartmentModel::findOrFail($newDepartmentId);
        $position = $newPositionId ? PositionModel::findOrFail($newPositionId) : null;

        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($employeeIds),
        ];

        DB::beginTransaction();
        try {
            foreach ($employeeIds as $employeeId) {
                try {
                    $employee = EmployeeModel::findOrFail($employeeId);
                    
                    $oldDepartment = $employee->department->name ?? 'Unknown';
                    $oldPosition = $employee->position->title ?? 'Unknown';

                    $employee->department_id = $newDepartmentId;
                    
                    if ($position) {
                        // Validate salary range for new position
                        if ($employee->current_salary < $position->base_salary_min || 
                            $employee->current_salary > $position->base_salary_max) {
                            $results['failed'][] = [
                                'id' => $employeeId,
                                'name' => $employee->full_name,
                                'error' => 'Current salary outside new position range',
                            ];
                            continue;
                        }
                        $employee->position_id = $newPositionId;
                    }

                    // Clear manager if they're not in the new department hierarchy
                    if ($employee->manager && $employee->manager->department_id !== $newDepartmentId) {
                        $employee->manager_id = null;
                    }

                    $note = "Bulk transfer from {$oldDepartment}";
                    if ($position) {
                        $note .= " ({$oldPosition}) to {$department->name} ({$position->title})";
                    } else {
                        $note .= " to {$department->name}";
                    }
                    
                    $employee->notes = ($employee->notes ?? '') . "\n[" . now()->format('Y-m-d H:i:s') . "] {$note}";
                    $employee->save();

                    $results['success'][] = [
                        'id' => $employeeId,
                        'name' => $employee->full_name,
                        'old_department' => $oldDepartment,
                        'new_department' => $department->name,
                        'old_position' => $oldPosition,
                        'new_position' => $position?->title,
                    ];

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'id' => $employeeId,
                        'name' => $employee->full_name ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function bulkSalaryAdjustment(array $employeeIds, float $adjustmentPercentage, string $reason = ''): array
    {
        $validator = Validator::make([
            'employee_ids' => $employeeIds,
            'adjustment_percentage' => $adjustmentPercentage,
        ], [
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'adjustment_percentage' => 'required|numeric|min:-50|max:100',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($employeeIds),
            'total_adjustment' => 0,
        ];

        DB::beginTransaction();
        try {
            foreach ($employeeIds as $employeeId) {
                try {
                    $employee = EmployeeModel::with('position')->findOrFail($employeeId);
                    
                    $oldSalary = $employee->current_salary;
                    $newSalary = $oldSalary * (1 + ($adjustmentPercentage / 100));

                    // Validate against position salary range
                    if ($employee->position) {
                        if ($newSalary < $employee->position->base_salary_min || 
                            $newSalary > $employee->position->base_salary_max) {
                            $results['failed'][] = [
                                'id' => $employeeId,
                                'name' => $employee->full_name,
                                'error' => 'New salary outside position range',
                                'old_salary' => $oldSalary,
                                'calculated_salary' => $newSalary,
                            ];
                            continue;
                        }
                    }

                    $employee->current_salary = $newSalary;
                    
                    $note = "Bulk salary adjustment: {$adjustmentPercentage}% (K{$oldSalary} → K{$newSalary})";
                    if ($reason) {
                        $note .= ". Reason: {$reason}";
                    }
                    
                    $employee->notes = ($employee->notes ?? '') . "\n[" . now()->format('Y-m-d H:i:s') . "] {$note}";
                    $employee->save();

                    $adjustment = $newSalary - $oldSalary;
                    $results['total_adjustment'] += $adjustment;

                    $results['success'][] = [
                        'id' => $employeeId,
                        'name' => $employee->full_name,
                        'old_salary' => $oldSalary,
                        'new_salary' => $newSalary,
                        'adjustment' => $adjustment,
                    ];

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'id' => $employeeId,
                        'name' => $employee->full_name ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function bulkAssignManager(array $employeeIds, int $managerId): array
    {
        $validator = Validator::make([
            'employee_ids' => $employeeIds,
            'manager_id' => $managerId,
        ], [
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'manager_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $manager = EmployeeModel::findOrFail($managerId);
        
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($employeeIds),
        ];

        DB::beginTransaction();
        try {
            foreach ($employeeIds as $employeeId) {
                try {
                    // Prevent self-assignment
                    if ($employeeId == $managerId) {
                        $results['failed'][] = [
                            'id' => $employeeId,
                            'name' => $manager->full_name,
                            'error' => 'Cannot assign employee as their own manager',
                        ];
                        continue;
                    }

                    $employee = EmployeeModel::findOrFail($employeeId);
                    
                    // Validate manager is in same or parent department
                    if (!$this->isValidManagerAssignment($employee, $manager)) {
                        $results['failed'][] = [
                            'id' => $employeeId,
                            'name' => $employee->full_name,
                            'error' => 'Manager must be in same or parent department',
                        ];
                        continue;
                    }

                    $oldManager = $employee->manager?->full_name ?? 'None';
                    $employee->manager_id = $managerId;
                    
                    $note = "Bulk manager assignment: {$oldManager} → {$manager->full_name}";
                    $employee->notes = ($employee->notes ?? '') . "\n[" . now()->format('Y-m-d H:i:s') . "] {$note}";
                    $employee->save();

                    $results['success'][] = [
                        'id' => $employeeId,
                        'name' => $employee->full_name,
                        'old_manager' => $oldManager,
                        'new_manager' => $manager->full_name,
                    ];

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'id' => $employeeId,
                        'name' => $employee->full_name ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function bulkExport(array $employeeIds, string $format = 'csv'): string
    {
        $employees = EmployeeModel::with(['department', 'position', 'manager'])
            ->whereIn('id', $employeeIds)
            ->get();

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($employees);
            case 'excel':
                return $this->exportToExcel($employees);
            case 'pdf':
                return $this->exportToPdf($employees);
            default:
                throw new \InvalidArgumentException('Unsupported export format');
        }
    }

    private function canTransitionStatus(string $currentStatus, string $newStatus): bool
    {
        $validTransitions = [
            'active' => ['inactive', 'terminated', 'suspended'],
            'inactive' => ['active', 'terminated'],
            'suspended' => ['active', 'terminated'],
            'terminated' => [], // Terminal state
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    private function updateUserAccountStatus(User $user, string $employmentStatus): void
    {
        switch ($employmentStatus) {
            case 'terminated':
            case 'suspended':
                $user->update(['status' => 'inactive']);
                $user->tokens()->delete(); // Revoke all tokens
                break;
            case 'active':
                $user->update(['status' => 'active']);
                break;
            case 'inactive':
                $user->update(['status' => 'inactive']);
                break;
        }
    }

    private function isValidManagerAssignment(EmployeeModel $employee, EmployeeModel $manager): bool
    {
        // Manager can be in the same department or any parent department
        if ($manager->department_id === $employee->department_id) {
            return true;
        }

        // Check if manager is in a parent department
        $department = $employee->department;
        while ($department && $department->parent_department_id) {
            if ($manager->department_id === $department->parent_department_id) {
                return true;
            }
            $department = $department->parentDepartment;
        }

        return false;
    }

    private function exportToCsv($employees): string
    {
        $filename = 'employees_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        // Headers
        fputcsv($file, [
            'Employee ID', 'Employee Number', 'First Name', 'Last Name', 'Email',
            'Department', 'Position', 'Manager', 'Employment Status', 'Hire Date',
            'Current Salary', 'Years of Service'
        ]);

        // Data
        foreach ($employees as $employee) {
            fputcsv($file, [
                $employee->id,
                $employee->employee_number,
                $employee->first_name,
                $employee->last_name,
                $employee->email,
                $employee->department?->name,
                $employee->position?->title,
                $employee->manager?->full_name,
                $employee->employment_status,
                $employee->hire_date?->format('Y-m-d'),
                $employee->current_salary,
                $employee->years_of_service,
            ]);
        }

        fclose($file);
        return $filepath;
    }

    private function exportToExcel($employees): string
    {
        // Implementation would use a library like PhpSpreadsheet
        // For now, return CSV as fallback
        return $this->exportToCsv($employees);
    }

    private function exportToPdf($employees): string
    {
        // Implementation would use a library like TCPDF or DomPDF
        // For now, return CSV as fallback
        return $this->exportToCsv($employees);
    }
}