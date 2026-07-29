<?php

namespace Tests\Unit\Domain\Notification\Entities;

use App\Domain\Notification\Entities\NotificationPreferences;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationPreferencesTest extends TestCase
{
    private NotificationPreferences $preferences;

    protected function setUp(): void
    {
        $this->preferences = NotificationPreferences::createDefault(1);
    }

    public function test_create_default(): void
    {
        $this->assertEquals(1, $this->preferences->userId());
        $this->assertTrue($this->preferences->isEmailEnabled());
        $this->assertFalse($this->preferences->isSmsEnabled());
        $this->assertFalse($this->preferences->isPushEnabled());
        $this->assertTrue($this->preferences->isInAppEnabled());
        $this->assertEquals('instant', $this->preferences->digestFrequency());
    }

    public function test_default_enabled_channels(): void
    {
        $channels = $this->preferences->getEnabledChannels();
        $this->assertEquals(['email', 'in_app'], $channels);
    }

    public function test_enable_channel(): void
    {
        $this->preferences->enableChannel('sms');
        $this->assertTrue($this->preferences->isSmsEnabled());

        $this->preferences->enableChannel('push');
        $this->assertTrue($this->preferences->isPushEnabled());
    }

    public function test_disable_channel(): void
    {
        $this->preferences->disableChannel('email');
        $this->assertFalse($this->preferences->isEmailEnabled());
    }

    public function test_enable_channel_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->preferences->enableChannel('fax');
    }

    public function test_disable_channel_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->preferences->disableChannel('fax');
    }

    public function test_default_categories_all_enabled_except_marketing(): void
    {
        $this->assertTrue($this->preferences->isCategoryEnabled('wallet'));
        $this->assertTrue($this->preferences->isCategoryEnabled('commissions'));
        $this->assertTrue($this->preferences->isCategoryEnabled('withdrawals'));
        $this->assertTrue($this->preferences->isCategoryEnabled('subscriptions'));
        $this->assertTrue($this->preferences->isCategoryEnabled('referrals'));
        $this->assertTrue($this->preferences->isCategoryEnabled('workshops'));
        $this->assertTrue($this->preferences->isCategoryEnabled('ventures'));
        $this->assertTrue($this->preferences->isCategoryEnabled('bgf'));
        $this->assertTrue($this->preferences->isCategoryEnabled('points'));
        $this->assertTrue($this->preferences->isCategoryEnabled('security'));
        $this->assertFalse($this->preferences->isCategoryEnabled('marketing'));
    }

    public function test_enable_category(): void
    {
        $this->preferences->enableCategory('marketing');
        $this->assertTrue($this->preferences->isCategoryEnabled('marketing'));
    }

    public function test_disable_category(): void
    {
        $this->preferences->disableCategory('wallet');
        $this->assertFalse($this->preferences->isCategoryEnabled('wallet'));
    }

    public function test_is_category_enabled_returns_false_for_unknown(): void
    {
        $this->assertFalse($this->preferences->isCategoryEnabled('nonexistent'));
    }

    public function test_get_enabled_channels_after_enabling_all(): void
    {
        $this->preferences->enableChannel('sms');
        $this->preferences->enableChannel('push');

        $channels = $this->preferences->getEnabledChannels();
        $this->assertEquals(['email', 'sms', 'push', 'in_app'], $channels);
    }

    public function test_get_enabled_channels_when_all_disabled(): void
    {
        $this->preferences->disableChannel('email');
        $this->preferences->disableChannel('in_app');

        $this->assertEquals([], $this->preferences->getEnabledChannels());
    }
}
