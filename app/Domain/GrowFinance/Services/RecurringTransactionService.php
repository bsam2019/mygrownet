<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceRecurringTransactionModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecurringTransactionService
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Get all recurring transactions for a business
     */
    public function getForBusiness(User $user, ?string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = GrowFinanceRecurringTransactionModel::forBusiness($user->id)
            ->with(['account', 'vendor', 'customer']);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('next_due_date')->get();
    }

    /**
     * Create a new recurring transaction
     */
    public function create(User $user, array $data): GrowFinanceRecurringTransactionModel
    {
        return GrowFinanceRecurringTransactionModel::create([
            'business_id' => $user->id,
            'type' => $data['type'],
            'account_id' => $data['account_id'] ?? null,
            'vendor_id' => $data['vendor_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'description' => $data['description'],
            'category' => $data['category'] ?? null,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'frequency' => $data['frequency'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'next_due_date' => $data['start_date'],
            'max_occurrences' => $data['max_occurrences'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
        ]);
    }

    /**
     * Process all due recurring transactions for a user
     */
    public function processDueTransactions(User $user): array
    {
        $processed = [];
        $errors = [];

        $dueTransactions = GrowFinanceRecurringTransactionModel::forBusiness($user->id)
            ->dueToday()
            ->get();

        foreach ($dueTransactions as $recurring) {
            try {
                if (!$recurring->shouldProcess()) {
                    continue;
                }

                // Check subscription limits
                $check = $this->subscriptionService->canIncrement($user, 'transactions_per_month', 'growfinance');
                if (!$check['allowed']) {
                    $errors[] = [
                        'id' => $recurring->id,
                        'description' => $recurring->description,
                        'error' => $check['reason'] ?? 'Transaction limit reached',
                    ];
                    continue;
                }

                DB::transaction(function () use ($recurring, $user, &$processed) {
                    if ($recurring->type === 'expense') {
                        $this->createExpenseFromRecurring($recurring);
                    }
                    // TODO: Add income/invoice creation

                    // Update recurring transaction
                    $recurring->update([
                        'last_processed_date' => now(),
                        'next_due_date' => $recurring->calculateNextDueDate(),
                        'occurrences_count' => $recurring->occurrences_count + 1,
                    ]);

                    // Check if should deactivate
                    if (!$recurring->shouldProcess()) {
                        $recurring->update(['is_active' => false]);
                    }

                    $processed[] = [
                        'id' => $recurring->id,
                        'description' => $recurring->description,
                        'amount' => $recurring->amount,
                    ];
                });

                // Clear usage cache
                $this->subscriptionService->clearCache($user, 'growfinance');

            } catch (\Exception $e) {
                Log::error('Failed to process recurring transaction', [
                    'recurring_id' => $recurring->id,
                    'error' => $e->getMessage(),
                ]);
                $errors[] = [
                    'id' => $recurring->id,
                    'description' => $recurring->description,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'processed' => $processed,
            'errors' => $errors,
            'total_processed' => count($processed),
            'total_errors' => count($errors),
        ];
    }

    /**
     * Create an expense from a recurring transaction
     */
    private function createExpenseFromRecurring(GrowFinanceRecurringTransactionModel $recurring): GrowFinanceExpenseModel
    {
        return GrowFinanceExpenseModel::create([
            'business_id' => $recurring->business_id,
            'account_id' => $recurring->account_id,
            'vendor_id' => $recurring->vendor_id,
            'expense_date' => now(),
            'category' => $recurring->category,
            'description' => $recurring->description . ' (Recurring)',
            'amount' => $recurring->amount,
            'payment_method' => $recurring->payment_method,
            'notes' => $recurring->notes,
            'is_recurring' => true,
        ]);
    }

    /**
     * Pause a recurring transaction
     */
    public function pause(GrowFinanceRecurringTransactionModel $recurring): void
    {
        $recurring->update(['is_active' => false]);
    }

    /**
     * Resume a recurring transaction
     */
    public function resume(GrowFinanceRecurringTransactionModel $recurring): void
    {
        // If next due date is in the past, set it to today
        $nextDue = $recurring->next_due_date;
        if ($nextDue->lt(now())) {
            $nextDue = now();
        }

        $recurring->update([
            'is_active' => true,
            'next_due_date' => $nextDue,
        ]);
    }

    /**
     * Get upcoming recurring transactions
     */
    public function getUpcoming(User $user, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return GrowFinanceRecurringTransactionModel::forBusiness($user->id)
            ->active()
            ->where('next_due_date', '<=', now()->addDays($days))
            ->orderBy('next_due_date')
            ->get();
    }
}
