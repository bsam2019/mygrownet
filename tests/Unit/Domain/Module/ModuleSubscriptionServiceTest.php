<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\Entities\ModuleSubscription;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleSubscriptionService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\Money;
use App\Domain\Module\ValueObjects\SubscriptionId;
use App\Domain\Module\ValueObjects\SubscriptionTier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleSubscriptionServiceTest extends TestCase
{
    private ModuleSubscriptionService $service;
    private ModuleSubscriptionRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ModuleSubscriptionRepositoryInterface::class);
        $this->service = new ModuleSubscriptionService($this->repository);
    }

    #[Test]
    public function cancel_calls_cancel_and_save()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'pro',
            amount: Money::fromAmount(9900),
        );

        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->with(1, 'stockflow')
            ->willReturn($subscription);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($subscription);

        $this->service->cancel(1, ModuleId::fromString('stockflow'));

        $this->assertEquals('cancelled', $subscription->getStatus());
    }

    #[Test]
    public function cancel_throws_when_not_found()
    {
        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->with(1, 'stockflow')
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Subscription not found');

        $this->service->cancel(1, ModuleId::fromString('stockflow'));
    }

    #[Test]
    public function upgrade_calls_upgradeTier_and_save()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(1000),
        );

        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->with(1, 'stockflow')
            ->willReturn($subscription);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($subscription);

        $result = $this->service->upgrade(
            userId: 1,
            moduleId: ModuleId::fromString('stockflow'),
            newTier: SubscriptionTier::pro(),
            newAmount: Money::fromAmount(9900),
        );

        $this->assertEquals('pro', $result->getTier());
        $this->assertEquals(9900, $result->getAmount()->amount());
    }

    #[Test]
    public function upgrade_throws_when_not_found()
    {
        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->willReturn(null);

        $this->expectException(\DomainException::class);

        $this->service->upgrade(1, ModuleId::fromString('stockflow'), SubscriptionTier::pro(), Money::fromAmount(100));
    }

    #[Test]
    public function upgrade_throws_when_inactive()
    {
        $subscription = ModuleSubscription::createTrial(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'pro',
        );

        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->willReturn($subscription);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot upgrade inactive subscription');

        $this->service->upgrade(1, ModuleId::fromString('stockflow'), SubscriptionTier::pro(), Money::fromAmount(100));
    }

    #[Test]
    public function convertFromTrial_converts_and_saves()
    {
        $subscription = ModuleSubscription::createTrial(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'pro',
        );

        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->with(1, 'stockflow')
            ->willReturn($subscription);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($subscription);

        $result = $this->service->convertFromTrial(
            userId: 1,
            moduleId: ModuleId::fromString('stockflow'),
            amount: Money::fromAmount(9900),
            billingCycle: 'monthly',
        );

        $this->assertEquals('active', $result->getStatus());
        $this->assertEquals(9900, $result->getAmount()->amount());
    }

    #[Test]
    public function convertFromTrial_throws_when_not_found()
    {
        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->willReturn(null);

        $this->expectException(\DomainException::class);

        $this->service->convertFromTrial(1, ModuleId::fromString('stockflow'), Money::fromAmount(100), 'monthly');
    }

    #[Test]
    public function renewSubscription_finds_and_renews()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'pro',
            amount: Money::fromAmount(9900),
        );
        $subscription->setId(SubscriptionId::fromInt(1));

        $this->repository->expects($this->once())
            ->method('findById')
            ->with('1')
            ->willReturn($subscription);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($subscription);

        $originalExpiry = $subscription->getExpiresAt();

        $this->service->renewSubscription(SubscriptionId::fromInt(1));

        $this->assertGreaterThan($originalExpiry, $subscription->getExpiresAt());
    }

    #[Test]
    public function renewSubscription_throws_when_not_found()
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with('99')
            ->willReturn(null);

        $this->expectException(\DomainException::class);

        $this->service->renewSubscription(SubscriptionId::fromInt(99));
    }

    #[Test]
    public function processExpiredSubscriptions_renews_auto_renew()
    {
        $renewable = ModuleSubscription::create(
            userId: 1,
            moduleId: 'stockflow',
            subscriptionTier: 'pro',
            amount: Money::fromAmount(9900),
        );
        $renewable->setId(SubscriptionId::fromInt(1));

        $nonRenewable = ModuleSubscription::create(
            userId: 2,
            moduleId: 'growfinance',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(1000),
        );
        $nonRenewable->setId(SubscriptionId::fromInt(2));
        $nonRenewable->cancel();

        $this->repository->expects($this->once())
            ->method('findExpired')
            ->willReturn([$renewable, $nonRenewable]);

        $this->repository->expects($this->once())
            ->method('findById')
            ->with('1')
            ->willReturn($renewable);

        $this->repository->expects($this->exactly(2))
            ->method('save');

        $count = $this->service->processExpiredSubscriptions();

        $this->assertEquals(1, $count);
        $this->assertEquals('suspended', $nonRenewable->getStatus());
    }

    #[Test]
    public function processExpiredSubscriptions_handles_empty()
    {
        $this->repository->expects($this->once())
            ->method('findExpired')
            ->willReturn([]);

        $this->repository->expects($this->never())
            ->method('save');

        $this->assertEquals(0, $this->service->processExpiredSubscriptions());
    }

    #[Test]
    public function startCheckout_creates_pending_subscription_with_reference()
    {
        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->with(1, 'bizboost')
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (ModuleSubscription $subscription) {
                $this->assertEquals('pending', $subscription->getStatus());
                $this->assertNotNull($subscription->getProviderReference());
                $this->assertStringStartsWith('sub_1_', $subscription->getProviderReference());
                $this->assertNull($subscription->getExpiresAt());
            });

        $result = $this->service->startCheckout(
            userId: 1,
            moduleId: ModuleId::fromString('bizboost'),
            tier: SubscriptionTier::pro(),
            amount: Money::fromAmount(9900),
            billingCycle: 'annual',
        );

        $this->assertEquals('pending', $result->getStatus());
        $this->assertEquals('bizboost', $result->getModuleId());
        $this->assertEquals('pro', $result->getTier());
        $this->assertEquals('annual', $result->getBillingCycle());
    }

    #[Test]
    public function startCheckout_reuses_existing_pending_subscription()
    {
        $existing = ModuleSubscription::create(
            userId: 1,
            moduleId: 'bizboost',
            subscriptionTier: 'basic',
            amount: Money::fromAmount(1000),
        );
        $existing->setStatus('pending');

        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->with(1, 'bizboost')
            ->willReturn($existing);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($existing);

        $result = $this->service->startCheckout(
            userId: 1,
            moduleId: ModuleId::fromString('bizboost'),
            tier: SubscriptionTier::pro(),
            amount: Money::fromAmount(9900),
        );

        $this->assertSame($existing, $result);
        $this->assertEquals('pending', $result->getStatus());
        $this->assertEquals('pro', $result->getTier());
    }

    #[Test]
    public function startCheckout_throws_when_already_active()
    {
        $existing = ModuleSubscription::create(
            userId: 1,
            moduleId: 'bizboost',
            subscriptionTier: 'pro',
            amount: Money::fromAmount(9900),
        );
        $existing->markActive();

        $this->repository->expects($this->once())
            ->method('findByUserAndModule')
            ->with(1, 'bizboost')
            ->willReturn($existing);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User already has an active subscription to this module');

        $this->service->startCheckout(1, ModuleId::fromString('bizboost'), SubscriptionTier::pro(), Money::fromAmount(9900));
    }

    #[Test]
    public function activateOnPayment_finds_and_activates()
    {
        $subscription = ModuleSubscription::create(
            userId: 1,
            moduleId: 'bizboost',
            subscriptionTier: 'pro',
            amount: Money::fromAmount(9900),
        );
        $subscription->setStatus('pending');
        $subscription->setProviderReference('sub_1_12345');

        $this->repository->expects($this->once())
            ->method('findByProviderReference')
            ->with('sub_1_12345')
            ->willReturn($subscription);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($subscription);

        $result = $this->service->activateOnPayment('sub_1_12345');

        $this->assertEquals('active', $result->getStatus());
        $this->assertNotNull($result->getExpiresAt());
        $this->assertNull($result->getCancelledAt());
    }

    #[Test]
    public function activateOnPayment_throws_when_not_found()
    {
        $this->repository->expects($this->once())
            ->method('findByProviderReference')
            ->with('sub_1_999')
            ->willReturn(null);

        $this->expectException(\DomainException::class);

        $this->service->activateOnPayment('sub_1_999');
    }
}
