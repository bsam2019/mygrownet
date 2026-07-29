<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowMart\Services;

use App\Domain\GrowMart\Services\NotificationService;
use App\Domain\Notification\Core\Services\NotificationDataService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    private NotificationDataService $notifications;
    private NotificationService $service;

    protected function setUp(): void
    {
        $this->notifications = $this->createMock(NotificationDataService::class);
        $this->service = new NotificationService($this->notifications);
    }

    private function createUserMock(int $id): \App\Models\User
    {
        $user = $this->getMockBuilder(\App\Models\User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAuthIdentifier'])
            ->getMock();
        $user->method('getAuthIdentifier')->willReturn($id);
        $user->id = $id;
        return $user;
    }

    #[Test]
    public function notify_delegates_with_growmart_module(): void
    {
        $user = $this->createUserMock(42);

        $model = $this->createStub(\App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel::class);

        $this->notifications->expects($this->once())
            ->method('create')
            ->with(
                userId: 42,
                type: 'growmart.order_placed',
                title: 'Order Placed',
                message: 'Your order GM-ABC has been placed.',
                module: 'growmart',
                actionUrl: '/orders/1',
                actionText: 'View Order',
                category: 'orders',
                priority: 'normal',
                data: ['order_id' => 1],
            )
            ->willReturn($model);

        $result = $this->service->notify(
            $user,
            'growmart.order_placed',
            'Order Placed',
            'Your order GM-ABC has been placed.',
            '/orders/1',
            'View Order',
            'orders',
            'normal',
            ['order_id' => 1],
        );

        $this->assertSame($model, $result);
    }

    #[Test]
    public function notify_uses_default_category_and_priority(): void
    {
        $user = $this->createUserMock(7);

        $model = $this->createStub(\App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel::class);

        $this->notifications->expects($this->once())
            ->method('create')
            ->with(
                userId: 7,
                type: 'growmart.test',
                title: 'Test Title',
                message: 'Test Message',
                module: 'growmart',
                actionUrl: null,
                actionText: null,
                category: 'orders',
                priority: 'normal',
                data: [],
            )
            ->willReturn($model);

        $this->service->notify($user, 'growmart.test', 'Test Title', 'Test Message');
    }

    #[Test]
    public function notify_passes_optional_parameters(): void
    {
        $user = $this->createUserMock(3);

        $model = $this->createStub(\App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel::class);

        $this->notifications->expects($this->once())
            ->method('create')
            ->with(
                userId: 3,
                type: 'growmart.alert',
                title: 'Alert',
                message: 'Low stock',
                module: 'growmart',
                actionUrl: '/inventory',
                actionText: 'Check',
                category: 'inventory',
                priority: 'high',
                data: ['product_id' => 5],
            )
            ->willReturn($model);

        $this->service->notify(
            $user,
            'growmart.alert',
            'Alert',
            'Low stock',
            '/inventory',
            'Check',
            'inventory',
            'high',
            ['product_id' => 5],
        );
    }
}
