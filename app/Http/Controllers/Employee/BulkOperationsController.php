<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\EmployeeBulkOperationsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BulkOperationsController extends Controller
{
    public function __construct(
        private EmployeeBulkOperationsService $bulkOperationsService
    ) {}

    public function updateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'status' => 'required|in:active,inactive,terminated,suspended',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $results = $this->bulkOperationsService->bulkUpdateStatus(
                $request->employee_ids,
                $request->status,
                $request->reason ?? ''
            );

            return response()->json([
                'success' => true,
                'message' => "Status updated for {$results['total']} employees",
                'results' => $results,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during bulk status update',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function transferDepartment(Request $request): JsonResponse
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
        ]);

        try {
            $results = $this->bulkOperationsService->bulkTransferDepartment(
                $request->employee_ids,
                $request->department_id,
                $request->position_id
            );

            return response()->json([
                'success' => true,
                'message' => "Department transfer completed for {$results['total']} employees",
                'results' => $results,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during bulk transfer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function adjustSalary(Request $request): JsonResponse
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'adjustment_percentage' => 'required|numeric|min:-50|max:100',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $results = $this->bulkOperationsService->bulkSalaryAdjustment(
                $request->employee_ids,
                $request->adjustment_percentage,
                $request->reason ?? ''
            );

            return response()->json([
                'success' => true,
                'message' => "Salary adjustment completed for {$results['total']} employees",
                'results' => $results,
                'total_adjustment' => $results['total_adjustment'],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during bulk salary adjustment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function assignManager(Request $request): JsonResponse
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'manager_id' => 'required|exists:employees,id',
        ]);

        try {
            $results = $this->bulkOperationsService->bulkAssignManager(
                $request->employee_ids,
                $request->manager_id
            );

            return response()->json([
                'success' => true,
                'message' => "Manager assignment completed for {$results['total']} employees",
                'results' => $results,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during bulk manager assignment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'format' => 'required|in:csv,excel,pdf',
        ]);

        try {
            $filePath = $this->bulkOperationsService->bulkExport(
                $request->employee_ids,
                $request->format
            );

            return response()->json([
                'success' => true,
                'message' => 'Export completed successfully',
                'download_url' => url('storage/exports/' . basename($filePath)),
                'file_path' => $filePath,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during export',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}