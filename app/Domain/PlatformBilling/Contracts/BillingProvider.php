<?php

namespace App\Domain\PlatformBilling\Contracts;

use App\Domain\Core\Contracts\ProviderContract;

interface BillingProvider extends ProviderContract
{
    public function getSubscription(int $subscriptionId): array;
    public function getPlan(int $planId): array;
    public function getInvoice(int $invoiceId): array;
    public function isActive(int $subscriptionId): bool;
}
