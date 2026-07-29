<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\Entities\ModuleSubscription;
use App\Domain\Module\ValueObjects\Money;
use App\Domain\Module\ValueObjects\SubscriptionId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleSubscriptionEntityTest extends TestCase
{
    #[Test]
    public function create_sets_initial_state()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'pro',
            amount: Money::fromAmount(9900),
            billingCycle: 'monthly',
        );

        $this->assertNull($subscription->getId());
        $this->assertEquals(1, $subscription->getUserId());
        $this->assertEquals('stockflow', $subscription->getModuleId());
        $this->assertEquals('pro', $subscription->getSubscriptionTier());
        $this->assertEquals('pro', $subscription->getTier());
        $this->assertEquals('active', $subscription->getStatus());
        $this->assertTrue($subscription->isAutoRenew());
        $this->assertEquals('monthly', $subscription->getBillingCycle());
        $this->assertEquals(9900, $subscription->getAmount()->amount());
        $this->assertFalse($subscription->isTrial());
        $this->assertNull($subscription->getTrialEndsAt());
        $this->assertNotNull($subscription->getStartedAt());
        $this->assertNotNull($subscription->getExpiresAt());
        $this->assertNull($subscription->getCancelledAt());
    }

    #[Test]
    public function create_with_annual_billing()
    {
        $subscription = ModuleSubscription::create(
            userId: 2,
            moduleId: 'growfinance',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(50000),
            billingCycle: 'annual',
        );

        $this->assertEquals('annual', $subscription->getBillingCycle());
    }

    #[Test]
    public function createTrial_sets_trial_state()
    {
        $subscription = ModuleSubscription::createTrial(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'pro',
            trialDays: 14,
        );

        $this->assertTrue($subscription->isTrial());
        $this->assertEquals('trial', $subscription->getStatus());
        $this->assertEquals('pro', $subscription->getTier());
        $this->assertNotNull($subscription->getTrialEndsAt());
        $this->assertTrue($subscription->getAmount()->isZero());
        $this->assertEquals('monthly', $subscription->getBillingCycle());
    }

    #[Test]
    public function createTrial_defaults_to_14_days()
    {
        $subscription = ModuleSubscription::createTrial(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'basic',
        );

        $this->assertNotNull($subscription->getTrialEndsAt());
    }

    #[Test]
    public function isActive_returns_true_when_active_and_not_expired()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $this->assertTrue($subscription->isActive());
    }

    #[Test]
    public function isActive_returns_false_when_trial()
    {
        $subscription = ModuleSubscription::createTrial(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
        );

        $this->assertFalse($subscription->isActive());
    }

    #[Test]
    public function cancel_updates_state()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $subscription->cancel();

        $this->assertEquals('cancelled', $subscription->getStatus());
        $this->assertFalse($subscription->isAutoRenew());
        $this->assertNotNull($subscription->getCancelledAt());
    }

    #[Test]
    public function suspend_updates_state()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $subscription->suspend();

        $this->assertEquals('suspended', $subscription->getStatus());
    }

    #[Test]
    public function reactivate_restores_active()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $subscription->suspend();
        $subscription->reactivate();

        $this->assertEquals('active', $subscription->getStatus());
    }

    #[Test]
    public function reactivate_cancelled_throws()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot reactivate a cancelled subscription');

        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $subscription->cancel();
        $subscription->reactivate();
    }

    #[Test]
    public function convertFromTrial_converts_to_active()
    {
        $subscription = ModuleSubscription::createTrial(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'pro',
        );

        $subscription->convertFromTrial(
            amount: Money::fromAmount(9900),
            billingCycle: 'monthly',
        );

        $this->assertEquals('active', $subscription->getStatus());
        $this->assertEquals(9900, $subscription->getAmount()->amount());
        $this->assertEquals('monthly', $subscription->getBillingCycle());
    }

    #[Test]
    public function convertFromTrial_throws_for_non_trial()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can only convert trial subscriptions');

        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $subscription->convertFromTrial(
            amount: Money::fromAmount(200),
            billingCycle: 'annual',
        );
    }

    #[Test]
    public function renew_adds_time_with_auto_renew()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $originalExpiry = $subscription->getExpiresAt();
        $subscription->renew();

        $this->assertGreaterThan($originalExpiry, $subscription->getExpiresAt());
    }

    #[Test]
    public function renew_throws_when_auto_renew_disabled()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Auto-renew is disabled');

        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $subscription->cancel();
        $subscription->renew();
    }

    #[Test]
    public function upgradeTier_changes_tier_and_amount()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(100),
        );

        $subscription->upgradeTier('pro', Money::fromAmount(9900));

        $this->assertEquals('pro', $subscription->getTier());
        $this->assertEquals(9900, $subscription->getAmount()->amount());
    }

    #[Test]
    public function setId_sets_id()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::zero(),
        );

        $this->assertNull($subscription->getId());

        $subscription->setId(SubscriptionId::fromInt(42));

        $this->assertEquals(42, $subscription->getId()->value());
    }

    #[Test]
    public function setLimits_updates_limits()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'test',
            subscriptionTier: 'basic',
            amount: Money::zero(),
        );

        $subscription->setLimits(10, 500);

        $this->assertEquals(10, $subscription->getUserLimit());
        $this->assertEquals(500, $subscription->getStorageLimitMb());
    }
}
