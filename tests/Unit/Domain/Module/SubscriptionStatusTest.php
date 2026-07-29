<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\SubscriptionStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubscriptionStatusTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertEquals('active', SubscriptionStatus::ACTIVE->value);
        $this->assertEquals('trial', SubscriptionStatus::TRIAL->value);
        $this->assertEquals('suspended', SubscriptionStatus::SUSPENDED->value);
        $this->assertEquals('cancelled', SubscriptionStatus::CANCELLED->value);
    }

    #[Test]
    public function isActive_returns_true_only_for_active()
    {
        $this->assertTrue(SubscriptionStatus::ACTIVE->isActive());
        $this->assertFalse(SubscriptionStatus::TRIAL->isActive());
        $this->assertFalse(SubscriptionStatus::SUSPENDED->isActive());
        $this->assertFalse(SubscriptionStatus::CANCELLED->isActive());
    }

    #[Test]
    public function isTrial_returns_true_only_for_trial()
    {
        $this->assertFalse(SubscriptionStatus::ACTIVE->isTrial());
        $this->assertTrue(SubscriptionStatus::TRIAL->isTrial());
        $this->assertFalse(SubscriptionStatus::SUSPENDED->isTrial());
        $this->assertFalse(SubscriptionStatus::CANCELLED->isTrial());
    }

    #[Test]
    public function isSuspended_returns_true_only_for_suspended()
    {
        $this->assertFalse(SubscriptionStatus::ACTIVE->isSuspended());
        $this->assertFalse(SubscriptionStatus::TRIAL->isSuspended());
        $this->assertTrue(SubscriptionStatus::SUSPENDED->isSuspended());
        $this->assertFalse(SubscriptionStatus::CANCELLED->isSuspended());
    }

    #[Test]
    public function isCancelled_returns_true_only_for_cancelled()
    {
        $this->assertFalse(SubscriptionStatus::ACTIVE->isCancelled());
        $this->assertFalse(SubscriptionStatus::TRIAL->isCancelled());
        $this->assertFalse(SubscriptionStatus::SUSPENDED->isCancelled());
        $this->assertTrue(SubscriptionStatus::CANCELLED->isCancelled());
    }

    #[Test]
    public function labels_are_readable()
    {
        $this->assertEquals('Active', SubscriptionStatus::ACTIVE->label());
        $this->assertEquals('Trial', SubscriptionStatus::TRIAL->label());
        $this->assertEquals('Suspended', SubscriptionStatus::SUSPENDED->label());
        $this->assertEquals('Cancelled', SubscriptionStatus::CANCELLED->label());
    }
}
