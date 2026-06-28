<?php

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Enums\TransactionStatus;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\ValueObjects\TransactionSource;
use App\Domain\Wallet\ValueObjects\Money;
use App\Models\User;

/**
 * Transaction Repository Interface
 * 
 * Defines the contract for transaction data access.
 * Implementations handle the actual data persistence.
 */
interface TransactionRepositoryInterface
{
    /**
     * Create a new transaction
     * 
     * @param array{
     *   user_id: int,
     *   type: TransactionType,
     *   source: TransactionSource,
     *   amount: Money,
     *   status: TransactionStatus,
     *   reference: string,
     *   description: string,
     *   metadata?: array
     * } $data
     */
    public function create(array $data): mixed;

    /**
     * Find transaction by reference number
     */
    public function findByReference(string $reference): ?object;

    /**
     * Check if transaction with reference exists
     */
    public function existsByReference(string $reference): bool;

    /**
     * Get user's transaction history
     * 
     * @param array{
     *   type?: TransactionType,
     *   source?: TransactionSource,
     *   status?: TransactionStatus,
     *   from_date?: \DateTimeInterface,
     *   to_date?: \DateTimeInterface,
     *   limit?: int,
     *   offset?: int
     * } $filters
     */
    public function getUserTransactions(User $user, array $filters = []): array;

    /**
     * Get total credits for user
     */
    public function getTotalCredits(User $user, ?TransactionSource $source = null): Money;

    /**
     * Get total debits for user
     */
    public function getTotalDebits(User $user, ?TransactionSource $source = null): Money;

    /**
     * Update transaction status
     */
    public function updateStatus(int $transactionId, TransactionStatus $status): bool;

    /**
     * Get transactions by module source
     */
    public function getBySource(TransactionSource $source, array $filters = []): array;
}
