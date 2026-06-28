<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

use Exception;

/**
 * Base exception for all GrowBiz domain errors
 */
class GrowBizException extends Exception
{
    protected string $errorCode;
    protected array $context;

    public function __construct(
        string $message,
        string $errorCode = 'GROWBIZ_ERROR',
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
            'context' => $this->context,
        ];
    }
}
