<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Payment;

interface PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment;

    public function save(Payment $payment): Payment;

    public function findByBusiness(int $businessId): array;

    public function findByPayable(string $payableType, int $payableId): array;

    public function findInDateRange(int $businessId, string $startDate, string $endDate): array;
}
