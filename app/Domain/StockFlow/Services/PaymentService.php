<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\PaymentTransaction;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\PaymentTransactionRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\PaymentTransactionId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Throwable;

class PaymentService
{
    public function __construct(private PaymentTransactionRepositoryInterface $transactionRepository) {}

    public function createTransaction(int $companyId, array $data): PaymentTransaction
    {
        try {
            $txn = PaymentTransaction::create(
                companyId: CompanyId::fromInt($companyId),
                payableType: $data['payable_type'],
                payableId: (int) $data['payable_id'],
                gateway: $data['gateway'] ?? 'cash',
                amount: (float) $data['amount'],
                currency: $data['currency'] ?? 'ZMW',
            );
            return $this->transactionRepository->save($txn);
        } catch (Throwable $e) {
            throw new OperationFailedException('create payment transaction', $e->getMessage());
        }
    }

    public function completeTransaction(int $id, int $companyId, string $transactionId, array $gatewayResponse = []): PaymentTransaction
    {
        $txn = $this->transactionRepository->findById(PaymentTransactionId::fromInt($id));
        if (!$txn) throw new OperationFailedException('complete transaction', 'Transaction not found');
        $txn->complete($transactionId, $gatewayResponse);
        return $this->transactionRepository->save($txn);
    }

    public function failTransaction(int $id, int $companyId, array $gatewayResponse = []): PaymentTransaction
    {
        $txn = $this->transactionRepository->findById(PaymentTransactionId::fromInt($id));
        if (!$txn) throw new OperationFailedException('fail transaction', 'Transaction not found');
        $txn->fail($gatewayResponse);
        return $this->transactionRepository->save($txn);
    }

    public function getTransactions(int $companyId): array
    {
        return $this->transactionRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function getTransactionsForPayable(int $companyId, string $payableType, int $payableId): array
    {
        return $this->transactionRepository->findByPayable($payableType, $payableId);
    }
}
