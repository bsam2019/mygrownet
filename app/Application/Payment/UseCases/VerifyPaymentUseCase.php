<?php

namespace App\Application\Payment\UseCases;

use App\Domain\Payment\Events\PaymentVerified;
use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;
use Illuminate\Support\Facades\Event;

class VerifyPaymentUseCase
{
    public function __construct(
        private readonly MemberPaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(int $paymentId, int $adminId, ?string $adminNotes = null): void
    {
        $payment = $this->paymentRepository->findById($paymentId);
        
        if (!$payment) {
            throw new \DomainException('Payment not found');
        }

        $payment->verify($adminId, $adminNotes);
        
        $this->paymentRepository->save($payment);

        Event::dispatch(new PaymentVerified(
            paymentId: $payment->id(),
            userId: $payment->userId(),
            verifiedBy: $adminId,
            amount: $payment->amount()->value(),
            paymentType: $payment->paymentType()->value,
            occurredAt: $payment->verifiedAt()
        ));
    }
}
