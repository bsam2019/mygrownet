<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\PaymentTransaction;
use App\Domain\StockFlow\ValueObjects\PaymentTransactionId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface PaymentTransactionRepositoryInterface
{
    public function findById(PaymentTransactionId $id): ?PaymentTransaction;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findByPayable(string $payableType, int $payableId): array;
    public function save(PaymentTransaction $transaction): PaymentTransaction;
}
