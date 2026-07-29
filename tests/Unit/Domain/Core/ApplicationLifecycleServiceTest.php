<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Entities\Application;
use App\Domain\Core\Exceptions\ProvisioningException;
use App\Domain\Core\Services\ApplicationLifecycleService;
use App\Domain\Core\Services\ApplicationService;
use App\Domain\Core\Services\EventDispatcher;
use App\Domain\Core\Services\LifecycleState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ApplicationLifecycleServiceTest extends TestCase
{
    private ApplicationLifecycleService $service;
    private Application $app;
    private object $org;

    protected function setUp(): void
    {
        $applicationService = $this->createStub(ApplicationService::class);
        $eventDispatcher = $this->createStub(EventDispatcher::class);

        $this->service = new ApplicationLifecycleService(
            $applicationService,
            $eventDispatcher,
        );

        $this->app = new Application(
            id: '1',
            name: 'TestApp',
            slug: 'test-app',
            type: 'module',
            url: null,
            isActive: true,
            category: 'business',
            accessModel: 'subscription',
            contextSupport: 'organization',
            requiresOrganizationContext: true,
            subscriptionRequired: false,
            lifecycle: 'active',
            operationalStatus: 'operational',
            replacementAppId: null,
            migrationDeadline: null,
            isVisible: true,
            config: [],
        );

        $this->org = new \stdClass();
        $this->org->id = 99;
        $this->org->name = 'TestOrg';
    }

    #[Test]
    public function currentState_defaults_to_active()
    {
        $this->assertEquals(
            LifecycleState::Active,
            $this->service->currentState('app-1', 1),
        );
    }

    #[Test]
    public function isValidTransition_delegates_to_enum()
    {
        $this->assertTrue(
            $this->service->isValidTransition(LifecycleState::Active, LifecycleState::Maintenance),
        );
        $this->assertFalse(
            $this->service->isValidTransition(LifecycleState::Archived, LifecycleState::Active),
        );
    }
}
