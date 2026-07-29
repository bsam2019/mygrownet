<?php

namespace Tests\Unit\Domain\Notification\Entities;

use App\Domain\Notification\Entities\Notification;
use App\Domain\Notification\ValueObjects\NotificationPriority;
use App\Domain\Notification\ValueObjects\NotificationType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function test_create(): void
    {
        $notification = Notification::create(
            id: 'uuid-123',
            userId: 1,
            type: NotificationType::fromString('wallet.topup'),
            title: 'Wallet Top-up',
            message: 'Your wallet has been credited with K500',
            actionUrl: '/wallet',
            actionText: 'View Wallet',
            data: ['amount' => 500],
            priority: NotificationPriority::NORMAL,
            expiresAt: null
        );

        $this->assertEquals('uuid-123', $notification->id());
        $this->assertEquals(1, $notification->userId());
        $this->assertTrue($notification->type()->equals(NotificationType::fromString('wallet.topup')));
        $this->assertEquals('Wallet Top-up', $notification->title());
        $this->assertEquals('Your wallet has been credited with K500', $notification->message());
        $this->assertEquals('/wallet', $notification->actionUrl());
        $this->assertEquals('View Wallet', $notification->actionText());
        $this->assertEquals(['amount' => 500], $notification->data());
        $this->assertEquals(NotificationPriority::NORMAL, $notification->priority());
        $this->assertFalse($notification->isRead());
        $this->assertFalse($notification->isArchived());
        $this->assertFalse($notification->isExpired());
        $this->assertNull($notification->readAt());
        $this->assertNotNull($notification->createdAt());
    }

    public function test_create_with_defaults(): void
    {
        $notification = Notification::create(
            id: 'uuid-456',
            userId: 2,
            type: NotificationType::fromString('commissions.earned'),
            title: 'Commission Earned',
            message: 'You earned K100 commission'
        );

        $this->assertNull($notification->actionUrl());
        $this->assertNull($notification->actionText());
        $this->assertEquals([], $notification->data());
        $this->assertEquals(NotificationPriority::NORMAL, $notification->priority());
        $this->assertNull($notification->readAt());
    }

    public function test_mark_as_read(): void
    {
        $notification = $this->createBasicNotification();
        $notification->markAsRead();

        $this->assertTrue($notification->isRead());
        $this->assertNotNull($notification->readAt());
    }

    public function test_mark_as_read_is_idempotent(): void
    {
        $notification = $this->createBasicNotification();
        $notification->markAsRead();
        $readAt = $notification->readAt();

        $notification->markAsRead();
        $this->assertSame($readAt, $notification->readAt());
    }

    public function test_mark_as_unread(): void
    {
        $notification = $this->createBasicNotification();
        $notification->markAsRead();
        $notification->markAsUnread();

        $this->assertFalse($notification->isRead());
        $this->assertNull($notification->readAt());
    }

    public function test_archive(): void
    {
        $notification = $this->createBasicNotification();
        $notification->archive();

        $this->assertTrue($notification->isArchived());
    }

    public function test_archive_is_idempotent(): void
    {
        $notification = $this->createBasicNotification();
        $notification->archive();
        $notification->archive();

        $this->assertTrue($notification->isArchived());
    }

    public function test_is_expired_returns_false_when_no_expiry(): void
    {
        $notification = $this->createBasicNotification();
        $this->assertFalse($notification->isExpired());
    }

    public function test_is_expired_returns_true_when_expired(): void
    {
        $notification = Notification::create(
            id: 'uuid-789',
            userId: 1,
            type: NotificationType::fromString('promo.offer'),
            title: 'Expired Offer',
            message: 'This offer has expired',
            expiresAt: new \DateTimeImmutable('-1 day')
        );

        $this->assertTrue($notification->isExpired());
    }

    public function test_is_expired_returns_false_when_not_expired(): void
    {
        $notification = Notification::create(
            id: 'uuid-101',
            userId: 1,
            type: NotificationType::fromString('promo.offer'),
            title: 'Active Offer',
            message: 'This offer is still active',
            expiresAt: new \DateTimeImmutable('+1 day')
        );

        $this->assertFalse($notification->isExpired());
    }

    public function test_high_priority(): void
    {
        $notification = Notification::create(
            id: 'uuid-111',
            userId: 1,
            type: NotificationType::fromString('security.alert'),
            title: 'Security Alert',
            message: 'New login detected',
            priority: NotificationPriority::HIGH
        );

        $this->assertEquals(NotificationPriority::HIGH, $notification->priority());
    }

    public function test_urgent_priority(): void
    {
        $notification = Notification::create(
            id: 'uuid-222',
            userId: 1,
            type: NotificationType::fromString('withdrawal.failed'),
            title: 'Withdrawal Failed',
            message: 'Your withdrawal could not be processed',
            priority: NotificationPriority::URGENT
        );

        $this->assertTrue($notification->priority()->isUrgent());
    }

    private function createBasicNotification(): Notification
    {
        return Notification::create(
            id: 'uuid-basic',
            userId: 1,
            type: NotificationType::fromString('test.notification'),
            title: 'Test',
            message: 'Test notification'
        );
    }
}
