<?php

namespace App\Domain\User\Exceptions;

use Exception;

class InvalidPhoneFormatException extends Exception
{
    public function __construct(string $message = "Invalid phone number format")
    {
        parent::__construct($message);
    }
}