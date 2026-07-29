<?php

namespace Tests\Unit\Domain\Notification\Services;

use App\Domain\Notification\Entities\NotificationPreferences;
use App\Domain\Notification\Services\NotificationDomainService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationDomainServiceTest extends TestCase
{
    private NotificationDomainService $service;

    protected function setUp(): void
    {
        $this->service = new NotificationDomainService();
    }

    public function test_create_notification(): void
    {
        $notification = $this->service->createNotification(
            userId: 1,
            type: 'wallet.topup',
            title: 'Wallet Top-up',
            message: 'Your wallet has been credited with K500',
            actionUrl: '/wallet',
            actionText: 'View',
            data: ['amount' => 500],
            priority: 'normal'
        );

        $this->assertNotNull($notification->id());
        $this->assertEquals(1, $notification->userId());
        $this->assertEquals('wallet.topup', $notification->type()->value());
        $this->assertEquals('Wallet Top-up', $notification->title());
        $this->assertEquals('/wallet', $notification->actionUrl());
        $this->assertEquals(['amount' => 500], $notification->data());
    }

    public function test_create_notification_with_minimal_fields(): void
    {
        $notification = $this->service->createNotification(
            userId: 1,
            type: 'test.event',
            title: 'Test',
            message: 'Test message'
        );

        $this->assertNull($notification->actionUrl());
        $this->assertNull($notification->actionText());
        $this->assertEquals([], $notification->data());
    }

    public function test_determine_channels_returns_forced_channels(): void
    {
        $preferences = NotificationPreferences::createDefault(1);
        $channels = $this->service->determineChannels(
            $preferences,
            'wallet',
            ['sms', 'push']
        );

        $this->assertEquals(['sms', 'push'], $channels);
    }

    public function test_determine_channels_returns_in_app_when_category_disabled(): void
    {
        $preferences = NotificationPreferences::createDefault(1);
        $preferences->disableCategory('marketing');

        $channels = $this->service->determineChannels($preferences, 'marketing');

        $this->assertEquals(['in_app'], $channels);
    }

    public function test_determine_channels_returns_enabled_channels(): void
    {
        $preferences = NotificationPreferences::createDefault(1);

        $channels = $this->service->determineChannels($preferences, 'wallet');

        $this->assertEquals(['email', 'in_app'], $channels);
    }

    public function test_should_send_notification_returns_true_when_category_enabled(): void
    {
        $preferences = NotificationPreferences::createDefault(1);
        $this->assertTrue($this->service->shouldSendNotification($preferences, 'wallet'));
    }

    public function test_should_send_notification_returns_false_when_category_disabled(): void
    {
        $preferences = NotificationPreferences::createDefault(1);
        $this->assertFalse($this->service->shouldSendNotification($preferences, 'marketing'));
    }

    public function test_create_notification_with_urgent_priority(): void
    {
        $notification = $this->service->createNotification(
            userId: 1,
            type: 'security.alert',
            title: 'Security Alert',
            message: 'New device login detected',
            priority: 'urgent'
        );

        $this->assertTrue($notification->priority()->isUrgent());
    }

    public function test_create_notification_with_high_priority(): void
    {
        $notification = $this->service->createNotification(
            userId: 1,
            type: 'withdrawal.failed',
            title: 'Withdrawal Failed',
            message: 'Transaction could not be completed',
            priority: 'high'
        );

        $this->assertTrue($notification->priority()->isHighOrAbove());
    }
}
