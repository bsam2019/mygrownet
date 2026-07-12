<?php

namespace App\Domain\PrimeEdge\Exceptions;

class InvoicePaymentException extends PrimeEdgeException
{
    public function __construct(string $message = 'Payment processing failed', array $context = [])
    {
        parent::__construct(
            message: $message,
            errorCode: 'INVOICE_PAYMENT_ERROR',
            context: $context
        );
    }
}
