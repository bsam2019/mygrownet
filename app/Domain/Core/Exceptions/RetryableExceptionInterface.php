<?php

namespace App\Domain\Core\Exceptions;

interface RetryableExceptionInterface
{
    public function retryDelayMs(int $attempt): int;
}
