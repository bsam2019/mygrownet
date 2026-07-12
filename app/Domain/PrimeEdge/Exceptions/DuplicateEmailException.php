<?php

namespace App\Domain\PrimeEdge\Exceptions;

class DuplicateEmailException extends PrimeEdgeException
{
    public function __construct(string $email)
    {
        parent::__construct(
            message: "A client with email {$email} already exists",
            errorCode: 'DUPLICATE_EMAIL',
            context: ['email' => $email]
        );
    }
}
