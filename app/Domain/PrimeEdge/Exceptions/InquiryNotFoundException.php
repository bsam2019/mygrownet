<?php

namespace App\Domain\PrimeEdge\Exceptions;

class InquiryNotFoundException extends PrimeEdgeException
{
    public function __construct(string $inquiryId = '')
    {
        parent::__construct(
            message: "Inquiry not found: {$inquiryId}",
            errorCode: 'INQUIRY_NOT_FOUND',
            context: ['inquiry_id' => $inquiryId]
        );
    }
}
