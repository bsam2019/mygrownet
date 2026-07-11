<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Exceptions;

use Exception;

class StockFlowException extends Exception
{
    protected string $errorCode;
    protected array $context;

    public function __construct(
        string $message,
        string $errorCode = 'STOCKFLOW_ERROR',
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getErrorCode(): string { return $this->errorCode; }
    public function getContext(): array { return $this->context; }
}
