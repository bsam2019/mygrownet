<?php

namespace App\Application\Payment\UseCases;

use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;

class ResetPaymentUseCase
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

        if ($payment->status()->isPending()) {
            throw new \DomainException('Payment is already pending');
        }

        $payment->resetToPending($adminId, $reason);
        
        $this->paymentRepository->save($payment);
    }
}
