<?php

namespace App\Domain\PlatformBilling\Services;

use App\Domain\Core\Services\OutboxService;
use App\Domain\Core\Services\PlatformContextResolver;
use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\PlatformBilling\Entities\Invoice;
use Illuminate\Support\Str;
use App\Domain\PlatformBilling\Entities\Subscription;
use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Entities\SubscriptionStatus;
use App\Domain\PlatformBilling\Events\GracePeriodExpiring;
use App\Domain\PlatformBilling\Events\InvoiceIssued;
use App\Domain\PlatformBilling\Events\PaymentDue;
use App\Domain\PlatformBilling\Events\SubscriptionCancelled;
use App\Domain\PlatformBilling\Events\SubscriptionCreated;
use App\Domain\PlatformBilling\Events\SubscriptionRenewed;
use App\Domain\PlatformBilling\Events\SubscriptionSuspended;
use App\Domain\PlatformBilling\Exceptions\BillingException;
use App\Domain\PlatformBilling\Repositories\InvoiceRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\SubscriptionRepositoryInterface;

class BillingService
{
    public function __construct(
        private PlanRepositoryInterface $plans,
        private SubscriptionRepositoryInterface $subscriptions,
        private InvoiceRepositoryInterface $invoices,
        private OutboxService $outbox,
        private PlatformContextResolver $contextResolver,
    ) {}

    public function createPlan(
        string $name,
        string $slug,
        float $monthlyPrice,
        float $annualPrice,
        int $siteLimit,
        int $storageLimitMb,
        int $teamMemberLimit,
        ?int $clientLimit = null,
        array $features = [],
        int $sortOrder = 0,
    ): SubscriptionPlan {
        $plan = SubscriptionPlan::create(
            name: $name,
            slug: $slug,
            monthlyPrice: $monthlyPrice,
            annualPrice: $annualPrice,
            siteLimit: $siteLimit,
            storageLimitMb: $storageLimitMb,
            teamMemberLimit: $teamMemberLimit,
            clientLimit: $clientLimit,
            features: $features,
            sortOrder: $sortOrder,
        );
        return $this->plans->save($plan);
    }

    public function createSubscription(
        int $userId,
        int $planId,
        float $amount,
        bool $isTrial = false,
        int $trialDays = 0,
    ): Subscription {
        $plan = $this->plans->findById($planId);
        if (!$plan) {
            throw BillingException::planNotFound($planId);
        }

        $subscription = Subscription::create(
            userId: $userId,
            planId: $planId,
            amount: $amount,
            isTrial: $isTrial,
            trialDays: $trialDays,
        );

        $subscription = $this->subscriptions->save($subscription);

        $this->publishEvent(new SubscriptionCreated(
            eventId: (string) Str::uuid(),
            eventVersion: '1.0',
            publisher: 'platform-billing',
            occurredAt: new \DateTimeImmutable(),
            correlationId: $this->getContext()->correlationId ?? '',
            causationId: null,
            context: $this->getContext(),
            payload: $subscription->toArray(),
        ));

        return $subscription;
    }

    public function activateSubscription(int $subscriptionId, int $durationMonths = 1): Subscription
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        if (!$subscription) {
            throw BillingException::subscriptionNotFound($subscriptionId);
        }

        $now = new \DateTimeImmutable();
        $endDate = $now->modify("+{$durationMonths} months");
        $subscription->activate($now, $endDate);

        $this->subscriptions->save($subscription);
        return $subscription;
    }

    public function renewSubscription(int $subscriptionId, int $durationMonths = 1): Subscription
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        if (!$subscription) {
            throw BillingException::subscriptionNotFound($subscriptionId);
        }

        $newEndDate = $subscription->endDate()
            ? $subscription->endDate()->modify("+{$durationMonths} months")
            : (new \DateTimeImmutable())->modify("+{$durationMonths} months");

        $subscription->renew($newEndDate);
        $this->subscriptions->save($subscription);

        $this->publishEvent(new SubscriptionRenewed(
            eventId: (string) Str::uuid(),
            eventVersion: '1.0',
            publisher: 'platform-billing',
            occurredAt: new \DateTimeImmutable(),
            correlationId: $this->getContext()->correlationId ?? '',
            causationId: null,
            context: $this->getContext(),
            payload: $subscription->toArray(),
        ));

        return $subscription;
    }

    public function suspendSubscription(int $subscriptionId, string $reason = 'Payment failure'): Subscription
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        if (!$subscription) {
            throw BillingException::subscriptionNotFound($subscriptionId);
        }

        $subscription->suspend($reason);
        $this->subscriptions->save($subscription);

        $this->publishEvent(new SubscriptionSuspended(
            eventId: (string) Str::uuid(),
            eventVersion: '1.0',
            publisher: 'platform-billing',
            occurredAt: new \DateTimeImmutable(),
            correlationId: $this->getContext()->correlationId ?? '',
            causationId: null,
            context: $this->getContext(),
            payload: $subscription->toArray(),
        ));

        return $subscription;
    }

    public function cancelSubscription(int $subscriptionId, ?string $reason = null): Subscription
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        if (!$subscription) {
            throw BillingException::subscriptionNotFound($subscriptionId);
        }

        $subscription->cancel($reason);
        $this->subscriptions->save($subscription);

        $this->publishEvent(new SubscriptionCancelled(
            eventId: (string) Str::uuid(),
            eventVersion: '1.0',
            publisher: 'platform-billing',
            occurredAt: new \DateTimeImmutable(),
            correlationId: $this->getContext()->correlationId ?? '',
            causationId: null,
            context: $this->getContext(),
            payload: $subscription->toArray(),
        ));

        return $subscription;
    }

    public function reactivateSubscription(int $subscriptionId): Subscription
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        if (!$subscription) {
            throw BillingException::subscriptionNotFound($subscriptionId);
        }

        $subscription->reactivate();
        $this->subscriptions->save($subscription);
        return $subscription;
    }

    public function issueInvoice(int $subscriptionId, int $organizationId): Invoice
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        if (!$subscription) {
            throw BillingException::subscriptionNotFound($subscriptionId);
        }

        $plan = $this->plans->findById($subscription->planId());
        if (!$plan) {
            throw BillingException::planNotFound($subscription->planId());
        }

        $dueDate = (new \DateTimeImmutable())->modify('+30 days');
        $invoiceNumber = 'INV-' . strtoupper(bin2hex(random_bytes(4)));

        $invoice = Invoice::create(
            subscriptionId: $subscriptionId,
            organizationId: $organizationId,
            amount: $subscription->amount(),
            currency: 'ZMW',
            dueDate: $dueDate,
            description: "Subscription: {$plan->name()}",
            lineItems: [
                [
                    'description' => "{$plan->name()} subscription",
                    'quantity' => 1,
                    'unit_price' => $subscription->amount(),
                    'total' => $subscription->amount(),
                ],
            ],
        );

        $invoice->issue($invoiceNumber);
        $invoice = $this->invoices->save($invoice);

        $this->publishEvent(new InvoiceIssued(
            eventId: (string) Str::uuid(),
            eventVersion: '1.0',
            publisher: 'platform-billing',
            occurredAt: new \DateTimeImmutable(),
            correlationId: $this->getContext()->correlationId ?? '',
            causationId: null,
            context: $this->getContext(),
            payload: $invoice->toArray(),
        ));

        $this->publishEvent(new PaymentDue(
            eventId: (string) Str::uuid(),
            eventVersion: '1.0',
            publisher: 'platform-billing',
            occurredAt: new \DateTimeImmutable(),
            correlationId: $this->getContext()->correlationId ?? '',
            causationId: $invoice->id() !== null ? "invoice:{$invoice->id()}" : null,
            context: $this->getContext(),
            payload: [
                'invoice_id' => $invoice->id(),
                'subscription_id' => $subscriptionId,
                'amount' => $invoice->amount(),
                'currency' => $invoice->currency(),
                'due_date' => $dueDate->format(\DateTimeInterface::ATOM),
            ],
        ));

        return $invoice;
    }

    public function handlePaymentFailure(int $subscriptionId, int $maxFailures = 3): void
    {
        $subscription = $this->subscriptions->findById($subscriptionId);
        if (!$subscription) {
            throw BillingException::subscriptionNotFound($subscriptionId);
        }

        $subscription->markPaymentFailed();
        $this->subscriptions->save($subscription);

        if ($subscription->failureCount() >= $maxFailures) {
            $this->publishEvent(new GracePeriodExpiring(
                eventId: (string) Str::uuid(),
                eventVersion: '1.0',
                publisher: 'platform-billing',
                occurredAt: new \DateTimeImmutable(),
                correlationId: $this->getContext()->correlationId ?? '',
                causationId: null,
                context: $this->getContext(),
                payload: [
                    'subscription_id' => $subscriptionId,
                    'failure_count' => $subscription->failureCount(),
                    'max_failures' => $maxFailures,
                ],
            ));
        }
    }

    public function markInvoicePaid(int $invoiceId, \DateTimeImmutable $paidAt): Invoice
    {
        $invoice = $this->invoices->findById($invoiceId);
        if (!$invoice) {
            throw BillingException::invoiceGenerationFailed("Invoice not found: {$invoiceId}");
        }

        $invoice->markPaid($paidAt);
        return $this->invoices->save($invoice);
    }

    public function processOverdueInvoices(): int
    {
        $overdue = $this->invoices->findOverdue();
        foreach ($overdue as $invoice) {
            $invoice->markOverdue();
            $this->invoices->save($invoice);
        }
        return count($overdue);
    }

    public function processExpiringSubscriptions(int $withinDays = 7): array
    {
        $expiring = $this->subscriptions->findExpiring($withinDays);
        $processed = [];
        foreach ($expiring as $subscription) {
            $processed[] = $subscription->id();
        }
        return $processed;
    }

    private function publishEvent(object $event): void
    {
        try {
            $this->outbox->insert(
                eventName: $event->eventName,
                payload: $event->toArray(),
                context: $this->getContext()->toArray(),
                publisher: 'platform-billing',
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function getContext(): PlatformContext
    {
        try {
            return $this->contextResolver->current();
        } catch (\Throwable) {
            return new PlatformContext(
                correlationId: '',
                userId: null,
                organizationId: null,
                applicationId: null,
            );
        }
    }
}
