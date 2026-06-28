<?php

namespace App\Domain\BizBoost\Contracts;

interface OmnichannelRouterInterface
{
    public function send(int $userId, string $phone, string $message, array $options = []): array;
    public function checkWhatsAppStatus(string $phone): bool;
}
