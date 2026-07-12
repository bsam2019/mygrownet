<?php

namespace App\Domain\BizBoost\Contracts;

interface SmsGatewayInterface
{
    public function send(string $to, string $message, array $options = []): array;
    public function sendBulk(array $recipients, string $message, array $options = []): array;
    public function getDeliveryStatus(string $messageId): string;
    public function getBalance(): float;
}
