<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\PaymentAllocation;

interface PaymentAllocationRepositoryInterface
{
    public function findById(int $id): ?PaymentAllocation;

    public function save(PaymentAllocation $allocation): PaymentAllocation;

    public function findByPayment(int $paymentId): array;

    public function findByInvoice(int $invoiceId): array;

    public function delete(int $id): void;
}
