<?php

namespace App\Domain\Financial\Exceptions;

use Exception;

class DuplicateTransactionException extends Exception
{
    public function __construct(string $message = 'Duplicate transaction detected')
    {
        parent::__construct($message);
    }
}
