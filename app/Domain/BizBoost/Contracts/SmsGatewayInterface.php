<?php

namespace App\Domain\BizBoost\Contracts;

class SmsDispatchResult
{
    public function __construct(
        public bool $success,
        public string $messageId,
        public ?string $errorMessage = null,
        public float $costZmw = 0.0
    ) {}
}

interface SmsGatewayInterface
{
    /**
     * Dispatch single or bulk SMS.
     */
    public function sendSms(string $to, string $message, ?string $senderId = null): SmsDispatchResult;

    /**
     * Retrieve current gateway SMS credit balance.
     */
    public function getBalance(): float;

    /**
     * Unique name identifier of the SMS provider (e.g. 'africala', 'twilio', 'termii', 'mock').
     */
    public function getProviderName(): string;
}
