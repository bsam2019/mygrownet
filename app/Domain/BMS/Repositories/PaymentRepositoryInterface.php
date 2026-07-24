<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Payment;

interface PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment;

    public function save(Payment $payment): Payment;

    public function findByCompany(int $companyId): array;

    public function findByCustomer(int $customerId): array;

    public function findUnallocatedByCustomer(int $customerId): array;

    public function getDailySummary(int $companyId, string $date): array;

    public function getCustomerPaymentSummary(int $customerId): array;
}
