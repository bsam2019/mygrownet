<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Services;

interface PaymentGatewayInterface
{
    public function initiatePayment(
        int $amountInNgwee,
        string $phoneNumber,
        string $reference,
        string $description,
    ): PaymentResult;

    public function checkStatus(string $transactionId): PaymentResult;

    public function getProviderName(): string;
}
