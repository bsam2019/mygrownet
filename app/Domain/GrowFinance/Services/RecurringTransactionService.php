<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Expense;
use App\Domain\GrowFinance\Entities\RecurringTransaction;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Domain\GrowFinance\Repositories\RecurringTransactionRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\PaymentMethod;
use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecurringTransactionService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private RecurringTransactionRepositoryInterface $recurringTransactionRepo,
        private ExpenseRepositoryInterface $expenseRepo,
    ) {}

    public function getForBusiness(int $businessId, ?string $type = null): array
    {
        $transactions = $type
            ? $this->recurringTransactionRepo->findByType($businessId, $type)
            : $this->recurringTransactionRepo->findByBusiness($businessId);

        return array_map(fn(RecurringTransaction $t) => $t->toArray(), $transactions);
    }

    public function create(int $userId, array $data): array
    {
        $transaction = $this->recurringTransactionRepo->save(new RecurringTransaction(
            id: null,
            businessId: $userId,
            type: $data['type'] ?? null,
            accountId: isset($data['account_id']) ? (int) $data['account_id'] : null,
            vendorId: isset($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            amount: (float) ($data['amount'] ?? 0),
            paymentMethod: $data['payment_method'] ?? null,
            frequency: $data['frequency'] ?? null,
            startDate: isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null,
            endDate: isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null,
            nextDueDate: isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null,
            lastProcessedDate: null,
            isActive: true,
            occurrencesCount: 0,
            maxOccurrences: isset($data['max_occurrences']) ? (int) $data['max_occurrences'] : null,
            notes: $data['notes'] ?? null,
            createdAt: null,
            updatedAt: null,
        ));

        return $transaction->toArray();
    }

    public function processDueTransactions(int $userId): array
    {
        $processed = [];
        $errors = [];
        $user = User::findOrFail($userId);

        $dueTransactions = $this->recurringTransactionRepo->findDueToday($userId);

        foreach ($dueTransactions as $recurring) {
            try {
                if (!$recurring->shouldProcess()) {
                    continue;
                }

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

                    $nextDue = $this->calculateNextDueDate($recurring);
                    $newOccurrencesCount = $recurring->occurrencesCount + 1;
                    $shouldBeActive = true;

                    if ($recurring->maxOccurrences !== null && $newOccurrencesCount >= $recurring->maxOccurrences) {
                        $shouldBeActive = false;
                    }
                    if ($recurring->endDate && $nextDue > $recurring->endDate) {
                        $shouldBeActive = false;
                    }

                    $updated = new RecurringTransaction(
                        id: $recurring->id,
                        businessId: $recurring->businessId,
                        type: $recurring->type,
                        accountId: $recurring->accountId,
                        vendorId: $recurring->vendorId,
                        customerId: $recurring->customerId,
                        description: $recurring->description,
                        category: $recurring->category,
                        amount: $recurring->amount,
                        paymentMethod: $recurring->paymentMethod,
                        frequency: $recurring->frequency,
                        startDate: $recurring->startDate,
                        endDate: $recurring->endDate,
                        nextDueDate: $nextDue,
                        lastProcessedDate: new \DateTimeImmutable('now'),
                        isActive: $shouldBeActive,
                        occurrencesCount: $newOccurrencesCount,
                        maxOccurrences: $recurring->maxOccurrences,
                        notes: $recurring->notes,
                        createdAt: $recurring->createdAt,
                        updatedAt: new \DateTimeImmutable('now'),
                    );
                    $this->recurringTransactionRepo->save($updated);

                    $processed[] = [
                        'id' => $recurring->id,
                        'description' => $recurring->description,
                        'amount' => $recurring->amount,
                    ];
                });

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

    private function createExpenseFromRecurring(RecurringTransaction $recurring): array
    {
        $expense = $this->expenseRepo->save(new Expense(
            id: null,
            businessId: $recurring->businessId,
            vendorId: $recurring->vendorId,
            accountId: $recurring->accountId,
            expenseDate: new \DateTimeImmutable('now'),
            category: $recurring->category,
            description: ($recurring->description ?? '') . ' (Recurring)',
            amount: $recurring->amount,
            paymentMethod: $recurring->paymentMethod ? PaymentMethod::from($recurring->paymentMethod) : null,
            isRecurring: true,
            notes: $recurring->notes,
        ));

        return $expense->toArray();
    }

    public function pause(int $transactionId): void
    {
        $recurring = $this->recurringTransactionRepo->findById($transactionId);
        if (!$recurring) return;

        $this->recurringTransactionRepo->save(new RecurringTransaction(
            id: $recurring->id,
            businessId: $recurring->businessId,
            type: $recurring->type,
            accountId: $recurring->accountId,
            vendorId: $recurring->vendorId,
            customerId: $recurring->customerId,
            description: $recurring->description,
            category: $recurring->category,
            amount: $recurring->amount,
            paymentMethod: $recurring->paymentMethod,
            frequency: $recurring->frequency,
            startDate: $recurring->startDate,
            endDate: $recurring->endDate,
            nextDueDate: $recurring->nextDueDate,
            lastProcessedDate: $recurring->lastProcessedDate,
            isActive: false,
            occurrencesCount: $recurring->occurrencesCount,
            maxOccurrences: $recurring->maxOccurrences,
            notes: $recurring->notes,
            createdAt: $recurring->createdAt,
            updatedAt: new \DateTimeImmutable('now'),
        ));
    }

    public function resume(int $transactionId): void
    {
        $recurring = $this->recurringTransactionRepo->findById($transactionId);
        if (!$recurring) return;

        $nextDue = $recurring->nextDueDate;
        $now = new \DateTimeImmutable('now');
        if ($nextDue && $nextDue < $now) {
            $nextDue = $now;
        }

        $this->recurringTransactionRepo->save(new RecurringTransaction(
            id: $recurring->id,
            businessId: $recurring->businessId,
            type: $recurring->type,
            accountId: $recurring->accountId,
            vendorId: $recurring->vendorId,
            customerId: $recurring->customerId,
            description: $recurring->description,
            category: $recurring->category,
            amount: $recurring->amount,
            paymentMethod: $recurring->paymentMethod,
            frequency: $recurring->frequency,
            startDate: $recurring->startDate,
            endDate: $recurring->endDate,
            nextDueDate: $nextDue,
            lastProcessedDate: $recurring->lastProcessedDate,
            isActive: true,
            occurrencesCount: $recurring->occurrencesCount,
            maxOccurrences: $recurring->maxOccurrences,
            notes: $recurring->notes,
            createdAt: $recurring->createdAt,
            updatedAt: new \DateTimeImmutable('now'),
        ));
    }

    public function getUpcoming(int $userId, int $days = 30): array
    {
        $transactions = $this->recurringTransactionRepo->findActive($userId);

        $cutoff = new \DateTimeImmutable("+{$days} days");
        $upcoming = array_filter(
            $transactions,
            fn(RecurringTransaction $t) => $t->nextDueDate && $t->nextDueDate <= $cutoff
        );

        usort($upcoming, fn(RecurringTransaction $a, RecurringTransaction $b) =>
            ($a->nextDueDate?->getTimestamp() ?? 0) <=> ($b->nextDueDate?->getTimestamp() ?? 0)
        );

        return array_map(fn(RecurringTransaction $t) => $t->toArray(), $upcoming);
    }

    private function calculateNextDueDate(RecurringTransaction $recurring): \DateTimeImmutable
    {
        $current = $recurring->nextDueDate ?? new \DateTimeImmutable('now');

        return match ($recurring->frequency) {
            'daily' => $current->modify('+1 day'),
            'weekly' => $current->modify('+1 week'),
            'biweekly' => $current->modify('+2 weeks'),
            'monthly' => $current->modify('+1 month'),
            'quarterly' => $current->modify('+3 months'),
            'yearly' => $current->modify('+1 year'),
            default => $current->modify('+1 month'),
        };
    }
}
