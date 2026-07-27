<?php

namespace App\Domain\PlatformBilling\Listeners;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\PlatformBilling\Services\BillingService;

class HandlePaymentCollectionFailed
{
    use InboxAware;

    public function __construct(
        private BillingService $billing,
    ) {}

    public function handle(array $payload): void
    {
        $subscriptionId = $payload['subscription_id'] ?? null;
        if (!$subscriptionId) {
            return;
        }

        $maxFailures = config('platform-billing.max_payment_failures', 3);
        $this->billing->handlePaymentFailure((int) $subscriptionId, $maxFailures);
    }
}
