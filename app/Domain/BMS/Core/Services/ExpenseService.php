<?php

declare(strict_types=1);

namespace App\Domain\BMS\Core\Services;

use App\Domain\BMS\Entities\Expense;
use App\Domain\BMS\Repositories\ExpenseRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepo,
        private AuditTrailService $auditTrail
    ) {}

    public function createExpense(array $data, int $companyId, int $userId): Expense
    {
        $expense = Expense::reconstitute([
            'company_id' => $companyId,
            'category_id' => $data['category_id'],
            'job_id' => $data['job_id'] ?? null,
            'description' => $data['description'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'receipt_path' => $data['receipt_path'] ?? null,
            'receipt_number' => $data['receipt_number'] ?? null,
            'expense_date' => $data['expense_date'],
            'approval_status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $userId,
        ]);
        $expense = $this->expenseRepo->save($expense);

        $this->auditTrail->log($companyId, $userId, 'expense', $expense->id, 'created', null, $expense->toArray());
        return $expense;
    }

    public function approveExpense(int $expenseId, int $userId): Expense
    {
        $expense = $this->expenseRepo->findById($expenseId);
        if (!$expense) throw new \InvalidArgumentException('Expense not found');

        $updated = Expense::reconstitute(array_merge($expense->toArray(), [
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now()->format('Y-m-d H:i:s'),
        ]));
        $this->expenseRepo->save($updated);

        $this->auditTrail->log($expense->companyId, $userId, 'expense', $expenseId, 'approved', ['approval_status' => $expense->approvalStatus], ['approval_status' => 'approved']);
        return $this->expenseRepo->findById($expenseId);
    }

    public function rejectExpense(int $expenseId, int $userId, string $reason): Expense
    {
        $expense = $this->expenseRepo->findById($expenseId);
        if (!$expense) throw new \InvalidArgumentException('Expense not found');

        $updated = Expense::reconstitute(array_merge($expense->toArray(), [
            'approval_status' => 'rejected',
            'approval_notes' => $reason,
            'approved_by' => $userId,
            'approved_at' => now()->format('Y-m-d H:i:s'),
        ]));
        $this->expenseRepo->save($updated);

        $this->auditTrail->log($expense->companyId, $userId, 'expense', $expenseId, 'rejected', ['approval_status' => $expense->approvalStatus], ['approval_status' => 'rejected', 'reason' => $reason]);
        return $this->expenseRepo->findById($expenseId);
    }

    public function uploadReceipt(UploadedFile $file, int $companyId): array
    {
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file->getClientOriginalName()));
        $uuid = Str::uuid();
        $s3Key = "cms/companies/{$companyId}/expenses/receipts/{$uuid}_{$sanitizedFilename}";

        Storage::disk('s3')->put($s3Key, file_get_contents($file->getRealPath()), [
            'ContentType' => $file->getClientMimeType(),
            'visibility' => 'private',
        ]);

        return ['path' => $s3Key, 'url' => Storage::disk('s3')->url($s3Key)];
    }
}
