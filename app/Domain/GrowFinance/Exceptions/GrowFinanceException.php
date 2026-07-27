<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Exceptions;

use App\Domain\Core\Contracts\NonRetryableExceptionInterface;

class GrowFinanceException extends \RuntimeException implements NonRetryableExceptionInterface
{
}

class AccountNotFoundException extends GrowFinanceException
{
}

class CustomerNotFoundException extends GrowFinanceException
{
}

class InvoiceNotFoundException extends GrowFinanceException
{
}

class InsufficientBalanceException extends GrowFinanceException
{
}

class InvalidTransitionException extends GrowFinanceException
{
}
