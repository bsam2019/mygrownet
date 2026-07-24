<?php

namespace App\Domain\GrowFinance\Exceptions;

class GrowFinanceException extends \RuntimeException
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
