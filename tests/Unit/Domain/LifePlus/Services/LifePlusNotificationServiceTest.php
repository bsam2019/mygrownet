<?php

namespace Tests\Unit\Domain\LifePlus\Services;

use App\Domain\LifePlus\Services\LifePlusNotificationService;
use App\Domain\Notification\Core\Services\NotificationDataService;
use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusNotificationServiceTest extends TestCase
{
    private NotificationDataService $notifications;
    private LifePlusNotificationService $service;

    protected function setUp(): void
    {
        $this->notifications = $this->createMock(NotificationDataService::class);
        $this->service = new LifePlusNotificationService($this->notifications);
    }

    private function stubNotificationModel(): NotificationModel
    {
        return $this->createStub(NotificationModel::class);
    }

    #[Test]
    public function create_delegates_to_notification_data_service()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('create')->with(
            42,
            'App\Notifications\LifePlusNotification',
            'Test Title',
            'Test message',
            'lifeplus',
            '/some-url',
            'Click',
            'info',
            'normal',
            ['key' => 'value'],
        )->willReturn($model);

        $result = $this->service->create(42, 'Test Title', 'Test message', 'info', '/some-url', 'Click', ['key' => 'value']);

        $this->assertSame($model, $result);
    }

    #[Test]
    public function create_uses_default_category()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('create')->with(
            1,
            'App\Notifications\LifePlusNotification',
            'Default',
            'Default cat',
            'lifeplus',
            null,
            null,
            'info',
            'normal',
            [],
        )->willReturn($model);

        $this->service->create(1, 'Default', 'Default cat');
    }

    #[Test]
    public function createWelcomeNotifications_creates_when_not_exists()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('existsForUser')->with(42, 'lifeplus', 'welcome')->willReturn(false);
        $this->notifications->expects($this->once())->method('create')->with(
            42,
            'App\Notifications\LifePlusNotification',
            'Welcome to LifePlus!',
            'Your LifePlus membership is now active. Explore health and wellness benefits.',
            'lifeplus',
            '/lifeplus/dashboard',
            'Go to LifePlus',
            'welcome',
            'normal',
            [],
        )->willReturn($model);

        $this->service->createWelcomeNotifications(42);
    }

    #[Test]
    public function createWelcomeNotifications_skips_when_already_exists()
    {
        $this->notifications->expects($this->once())->method('existsForUser')->with(42, 'lifeplus', 'welcome')->willReturn(true);
        $this->notifications->expects($this->never())->method('create');

        $this->service->createWelcomeNotifications(42);
    }

    #[Test]
    public function createPointsAwardedNotification_creates_with_points_message()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('create')->with(
            42,
            'App\Notifications\LifePlusNotification',
            'Points Awarded!',
            "You've earned 150 LifePlus points: Daily login bonus",
            'lifeplus',
            '/lifeplus/points',
            'View Points',
            'points',
            'normal',
            [],
        )->willReturn($model);

        $this->service->createPointsAwardedNotification(42, 'login', 150, 'Daily login bonus');
    }

    #[Test]
    public function createBenefitNotification_creates_with_benefit_name()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('create')->with(
            42,
            'App\Notifications\LifePlusNotification',
            'New Benefit: Health Check',
            'Free annual health checkup',
            'lifeplus',
            '/lifeplus/benefits',
            'View Benefits',
            'benefits',
            'normal',
            [],
        )->willReturn($model);

        $this->service->createBenefitNotification(42, 'Health Check', 'Free annual health checkup');
    }

    #[Test]
    public function createTierUpgradeNotification_creates_with_tier_name()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('create')->with(
            42,
            'App\Notifications\LifePlusNotification',
            'Tier Upgrade!',
            "Congratulations! You've been upgraded to Premium tier.",
            'lifeplus',
            '/lifeplus/tiers',
            'View Tier Benefits',
            'tier',
            'normal',
            [],
        )->willReturn($model);

        $this->service->createTierUpgradeNotification(42, 'Premium');
    }

    #[Test]
    public function createExpirationNotification_creates_with_expiration_message()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('create')->with(
            42,
            'App\Notifications\LifePlusNotification',
            'Expiring Soon',
            'Your Membership is expiring on 2026-09-30. Renew to keep enjoying LifePlus benefits.',
            'lifeplus',
            '/lifeplus/renew',
            'Renew Now',
            'expiration',
            'normal',
            [],
        )->willReturn($model);

        $this->service->createExpirationNotification(42, 'Membership', '2026-09-30');
    }

    #[Test]
    public function createMilestoneNotification_creates_with_milestone_name()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('create')->with(
            42,
            'App\Notifications\LifePlusNotification',
            'Milestone: 30-Day Streak',
            'You maintained a 30-day streak!',
            'lifeplus',
            '/lifeplus/milestones',
            'View Achievements',
            'milestones',
            'normal',
            [],
        )->willReturn($model);

        $this->service->createMilestoneNotification(42, '30-Day Streak', 'You maintained a 30-day streak!');
    }

    #[Test]
    public function createReminderNotification_creates_with_reminder_category()
    {
        $model = $this->stubNotificationModel();

        $this->notifications->expects($this->once())->method('create')->with(
            42,
            'App\Notifications\LifePlusNotification',
            'Drink Water',
            'Time to hydrate!',
            'lifeplus',
            '/lifeplus/habits',
            null,
            'reminders',
            'normal',
            [],
        )->willReturn($model);

        $this->service->createReminderNotification(42, 'Drink Water', 'Time to hydrate!', '/lifeplus/habits');
    }

    #[Test]
    public function getUnreadCount_delegates_to_notifications()
    {
        $builder = \Mockery::mock(Builder::class);
        $builder->shouldReceive('where')->with('module', 'lifeplus')->andReturnSelf();
        $builder->shouldReceive('whereNull')->with('read_at')->andReturnSelf();
        $builder->shouldReceive('count')->andReturn(3);

        $this->notifications->expects($this->once())->method('forUser')->with(42)->willReturn($builder);

        $this->assertSame(3, $this->service->getUnreadCount(42));
    }

    #[Test]
    public function getRecentNotifications_delegates_to_notifications()
    {
        $builder = \Mockery::mock(Builder::class);
        $builder->shouldReceive('where')->with('module', 'lifeplus')->andReturnSelf();
        $builder->shouldReceive('latest')->andReturnSelf();
        $builder->shouldReceive('limit')->with(2)->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect(['n1', 'n2']));

        $this->notifications->expects($this->once())->method('forUser')->with(42)->willReturn($builder);

        $result = $this->service->getRecentNotifications(42, 2);

        $this->assertCount(2, $result);
    }
}
