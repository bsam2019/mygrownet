<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\Enums\TransactionStatus;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\ValueObjects\TransactionSource;
use App\Domain\Wallet\ValueObjects\Money;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent Transaction Repository
 * 
 * Concrete implementation of TransactionRepositoryInterface using Eloquent ORM.
 * Handles transaction persistence and retrieval.
 */
class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Create a new transaction
     */
    public function create(array $data): mixed
    {
        $transactionData = [
            'user_id' => $data['user_id'],
            'transaction_type' => $data['type']->value,
            'transaction_source' => $data['source']->value(),
            'amount' => $data['amount']->amount(),
            'status' => $data['status']->value,
            'reference_number' => $data['reference'],
            'description' => $data['description'],
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add optional fields
        if (isset($data['metadata'])) {
            $transactionData['notes'] = json_encode($data['metadata']);
        }

        if (isset($data['module_reference'])) {
            $transactionData['module_reference'] = $data['module_reference'];
        }

        $id = DB::table('transactions')->insertGetId($transactionData);
        
        return DB::table('transactions')->find($id);
    }

    /**
     * Find transaction by reference number
     */
    public function findByReference(string $reference): ?object
    {
        return DB::table('transactions')
            ->where('reference_number', $reference)
            ->first();
    }

    /**
     * Check if transaction with reference exists
     */
    public function existsByReference(string $reference): bool
    {
        return DB::table('transactions')
            ->where('reference_number', $reference)
            ->exists();
    }

    /**
     * Get user's transaction history
     */
    public function getUserTransactions(User $user, array $filters = []): array
    {
        $query = DB::table('transactions')
            ->where('user_id', $user->id);

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('transaction_type', $filters['type']->value);
        }

        if (isset($filters['source'])) {
            $query->where('transaction_source', $filters['source']->value());
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']->value);
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']->format('Y-m-d H:i:s'));
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']->format('Y-m-d H:i:s'));
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        // Apply pagination
        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        if (isset($filters['offset'])) {
            $query->offset($filters['offset']);
        }

        return $query->get()->toArray();
    }

    /**
     * Get total credits for user
     */
    public function getTotalCredits(User $user, ?TransactionSource $source = null): Money
    {
        $query = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('amount', '>', 0);

        if ($source) {
            $query->where('transaction_source', $source->value());
        }

        $total = $query->sum('amount');
        
        return Money::fromKwacha($total);
    }

    /**
     * Get total debits for user
     */
    public function getTotalDebits(User $user, ?TransactionSource $source = null): Money
    {
        $query = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('amount', '<', 0);

        if ($source) {
            $query->where('transaction_source', $source->value());
        }

        $total = abs($query->sum('amount'));
        
        return Money::fromKwacha($total);
    }

    /**
     * Update transaction status
     */
    public function updateStatus(int $transactionId, TransactionStatus $status): bool
    {
        return DB::table('transactions')
            ->where('id', $transactionId)
            ->update([
                'status' => $status->value,
                'updated_at' => now(),
                'processed_at' => $status->isSuccessful() ? now() : null,
            ]) > 0;
    }

    /**
     * Get transactions by module source
     */
    public function getBySource(TransactionSource $source, array $filters = []): array
    {
        $query = DB::table('transactions')
            ->where('transaction_source', $source->value());

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']->value);
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']->format('Y-m-d H:i:s'));
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']->format('Y-m-d H:i:s'));
        }

        $query->orderBy('created_at', 'desc');

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get()->toArray();
    }
}
