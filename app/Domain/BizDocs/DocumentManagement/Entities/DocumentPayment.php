<?php

namespace App\Domain\BizDocs\DocumentManagement\Entities;

use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\PaymentMethod;
use DateTimeImmutable;

class DocumentPayment
{
    private function __construct(
        private ?int $id,
        private int $documentId,
        private DateTimeImmutable $paymentDate,
        private Money $amount,
        private PaymentMethod $paymentMethod,
        private ?string $referenceNumber,
        private ?string $notes,
        private ?int $receiptId = null
    ) {
        $this->validate();
    }

    public static function create(
        int $documentId,
        DateTimeImmutable $paymentDate,
        Money $amount,
        PaymentMethod $paymentMethod,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): self {
        return new self(
            null,
            $documentId,
            $paymentDate,
            $amount,
            $paymentMethod,
            $referenceNumber,
            $notes
        );
    }

    public static function fromPersistence(
        int $id,
        int $documentId,
        DateTimeImmutable $paymentDate,
        Money $amount,
        PaymentMethod $paymentMethod,
        ?string $referenceNumber,
        ?string $notes,
        ?int $receiptId = null
    ): self {
        return new self(
            $id,
            $documentId,
            $paymentDate,
            $amount,
            $paymentMethod,
            $referenceNumber,
            $notes,
            $receiptId
        );
    }

    private function validate(): void
    {
        if ($this->amount->amount() <= 0) {
            throw new \DomainException('Payment amount must be greater than zero');
        }
    }

    public function updateDetails(
        DateTimeImmutable $paymentDate,
        Money $amount,
        PaymentMethod $paymentMethod,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): void {
        if ($amount->amount() <= 0) {
            throw new \DomainException('Payment amount must be greater than zero');
        }

        $this->paymentDate = $paymentDate;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
        $this->referenceNumber = $referenceNumber;
        $this->notes = $notes;
    }

    // Getters
    public function id(): ?int
    {
        return $this->id;
    }

    public function documentId(): int
    {
        return $this->documentId;
    }

    public function paymentDate(): DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function paymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function referenceNumber(): ?string
    {
        return $this->referenceNumber;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function receiptId(): ?int
    {
        return $this->receiptId;
    }

    public function setReceiptId(int $receiptId): void
    {
        $this->receiptId = $receiptId;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'documentId' => $this->documentId,
            'paymentDate' => $this->paymentDate->format('Y-m-d'),
            'amount' => $this->amount->amount() / 100,
            'paymentMethod' => $this->paymentMethod->value(),
            'paymentMethodLabel' => $this->paymentMethod->label(),
            'referenceNumber' => $this->referenceNumber,
            'notes' => $this->notes,
            'receiptId' => $this->receiptId,
        ];
    }
}
