<?php

namespace App\Domain\Payment\Repositories;

use App\Domain\Payment\Entities\MemberPayment;

interface MemberPaymentRepositoryInterface
{
    public function save(MemberPayment $payment): MemberPayment;
    
    public function findById(int $id): ?MemberPayment;
    
    public function findByUserId(int $userId): array;
    
    public function findByReference(string $reference): ?MemberPayment;
    
    public function findPendingPayments(): array;
    
    public function findByStatus(string $status): array;
}
