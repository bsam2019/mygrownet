<?php

namespace App\Application\Payment\UseCases;

use App\Application\Payment\DTOs\SubmitPaymentDTO;
use App\Domain\Payment\Entities\MemberPayment;
use App\Domain\Payment\Events\PaymentSubmitted;
use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;
use App\Domain\Payment\ValueObjects\PaymentAmount;
use App\Domain\Payment\ValueObjects\PaymentMethod;
use App\Domain\Payment\ValueObjects\PaymentType;
use Illuminate\Support\Facades\Event;

class SubmitPaymentUseCase
{
    public function __construct(
        private readonly MemberPaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(SubmitPaymentDTO $dto): MemberPayment
    {
        // Check for duplicate reference
        $existingPayment = $this->paymentRepository->findByReference($dto->paymentReference);
        if ($existingPayment) {
            throw new \DomainException('Payment reference already exists');
        }

        // Create domain entity
        $payment = MemberPayment::create(
            userId: $dto->userId,
            amount: PaymentAmount::fromFloat($dto->amount),
            paymentMethod: PaymentMethod::fromString($dto->paymentMethod),
            paymentReference: $dto->paymentReference,
            phoneNumber: $dto->phoneNumber,
            paymentType: PaymentType::fromString($dto->paymentType),
            notes: $dto->notes
        );

        // Persist to database
        $savedPayment = $this->paymentRepository->save($payment);

        // Dispatch domain event
        Event::dispatch(new PaymentSubmitted(
            paymentId: $savedPayment->id(),
            userId: $savedPayment->userId(),
            amount: $savedPayment->amount()->value(),
            paymentType: $savedPayment->paymentType()->value,
            occurredAt: $savedPayment->createdAt()
        ));

        return $savedPayment;
    }
}
