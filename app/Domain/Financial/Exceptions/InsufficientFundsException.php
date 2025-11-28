<?php

namespace App\Domain\Financial\Exceptions;

use Exception;

class InsufficientFundsException extends Exception
{
    public function __construct(string $message = 'Insufficient funds for this transaction')
    {
        parent::__construct($message);
    }
}
