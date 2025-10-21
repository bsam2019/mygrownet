<?php

namespace App\Application\Payment\UseCases;

use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;

class RejectPaymentUseCase
{
    public function __construct(
        private readonly MemberPaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(int $paymentId, int $adminId, string $reason): void
    {
        $payment = $this->paymentRepository->findById($paymentId);
        
        if (!$payment) {
            throw new \DomainException('Payment not found');
        }

        $payment->reject($adminId, $reason);
        
        $this->paymentRepository->save($payment);
    }
}
