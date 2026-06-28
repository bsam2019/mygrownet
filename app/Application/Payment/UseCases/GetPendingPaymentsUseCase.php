<?php

namespace App\Application\Payment\UseCases;

use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;

class GetPendingPaymentsUseCase
{
    public function __construct(
        private readonly MemberPaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(): array
    {
        return $this->paymentRepository->findPendingPayments();
    }
}
