<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseService
{
    public function createExpense(array $data, int $companyId, int $userId): ExpenseModel
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            // Generate expense number
            $expenseNumber = $this->generateExpenseNumber($companyId);

            // Check if approval required
            $category = ExpenseCategoryModel::find($data['category_id']);
            $requiresApproval = $category->requires_approval && 
                               ($category->approval_limit === null || $data['amount'] > $category->approval_limit);

            $expense = ExpenseModel::create([
                'company_id' => $companyId,
                'expense_number' => $expenseNumber,
                'category_id' => $data['category_id'],
                'job_id' => $data['job_id'] ?? null,
                'description' => $data['description'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'receipt_number' => $data['receipt_number'] ?? null,
                'receipt_path' => $data['receipt_path'] ?? null,
                'expense_date' => $data['expense_date'],
                'requires_approval' => $requiresApproval,
                'approval_status' => $requiresApproval ? 'pending' : 'approved',
                'recorded_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            // If linked to job and approved, update job cost
            if ($expense->job_id && $expense->isApproved()) {
                $this->updateJobCost($expense->job_id, $expense->amount);
            }

            // Log audit trail
            app(AuditTrailService::class)->log(
                $companyId,
                $userId,
                'expense',
                $expense->id,
                'created',
                null,
                $expense->toArray()
            );

            return $expense;
        });
    }

    public function approveExpense(int $expenseId, int $userId): ExpenseModel
    {
        return DB::transaction(function () use ($expenseId, $userId) {
            $expense = ExpenseModel::findOrFail($expenseId);

            $oldData = $expense->toArray();

            $expense->update([
                'approval_status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            // Update job cost if linked
            if ($expense->job_id) {
                $this->updateJobCost($expense->job_id, $expense->amount);
            }

            // Log audit trail
            app(AuditTrailService::class)->log(
                $expense->company_id,
                $userId,
                'expense',
                $expense->id,
                'approved',
                $oldData,
                $expense->fresh()->toArray()
            );

            return $expense->fresh();
        });
    }

    public function rejectExpense(int $expenseId, int $userId, string $reason): ExpenseModel
    {
        return DB::transaction(function () use ($expenseId, $userId, $reason) {
            $expense = ExpenseModel::findOrFail($expenseId);

            $oldData = $expense->toArray();

            $expense->update([
                'approval_status' => 'rejected',
                'approved_by' => $userId,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Log audit trail
            app(AuditTrailService::class)->log(
                $expense->company_id,
                $userId,
                'expense',
                $expense->id,
                'rejected',
                $oldData,
                $expense->fresh()->toArray()
            );

            return $expense->fresh();
        });
    }

    public function uploadReceipt($file, int $companyId): array
    {
        $path = $file->store("cms/{$companyId}/receipts", 'public');

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
        ];
    }

    private function generateExpenseNumber(int $companyId): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastExpense = ExpenseModel::where('company_id', $companyId)
            ->where('expense_number', 'like', "EXP-{$year}-{$month}-%")
            ->orderBy('expense_number', 'desc')
            ->first();

        if ($lastExpense) {
            $lastNumber = (int) substr($lastExpense->expense_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "EXP-{$year}-{$month}-{$newNumber}";
    }

    private function updateJobCost(int $jobId, float $amount): void
    {
        $job = JobModel::find($jobId);
        if ($job) {
            $job->increment('overhead_cost', $amount);
            $job->increment('total_cost', $amount);
            
            // Recalculate profit
            $profit = ($job->actual_value ?? $job->quoted_value ?? 0) - $job->total_cost;
            $margin = $job->actual_value > 0 ? ($profit / $job->actual_value) * 100 : 0;
            
            $job->update([
                'profit_amount' => $profit,
                'profit_margin' => $margin,
            ]);
        }
    }
}
