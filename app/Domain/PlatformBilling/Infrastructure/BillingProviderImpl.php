<?php

namespace App\Domain\PlatformBilling\Infrastructure;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\PlatformBilling\Contracts\BillingProvider;
use App\Domain\PlatformBilling\Repositories\InvoiceRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\SubscriptionRepositoryInterface;

class BillingProviderImpl implements BillingProvider
{
    public function __construct(
        private PlanRepositoryInterface $plans,
        private SubscriptionRepositoryInterface $subscriptions,
        private InvoiceRepositoryInterface $invoices,
    ) {}

    public function capability(): string
    {
        return 'billing';
    }

    public function getSubscription(int $subscriptionId): array
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        return $subscription ? $subscription->toArray() : [];
    }

    public function getPlan(int $planId): array
    {
        $plan = $this->plans->findById($planId);
        return $plan ? $plan->toArray() : [];
    }

    public function getInvoice(int $invoiceId): array
    {
        $invoice = $this->invoices->findById($invoiceId);
        return $invoice ? $invoice->toArray() : [];
    }

    public function isActive(int $subscriptionId): bool
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        return $subscription !== null
            && $subscription->status()->value === 'active'
            && !$subscription->isOverdue();
    }
}
