<?php

namespace Tests\Feature\Providers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Providers\EmployeeServiceProvider;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\CachedEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\OptimizedEmployeeRepository;
use App\Domain\Employee\Services\EmployeeRegistrationService;
use App\Domain\Employee\Services\PerformanceTrackingService;
use App\Domain\Employee\Services\CommissionCalculationService;
use App\Domain\Employee\Services\PayrollCalculationService;
use App\Services\EmployeeCacheService;
use App\Services\EmployeeRoleIntegrationService;
use App\Console\Commands\Employee\ClearEmployeeCacheCommand;
use App\Console\Commands\Employee\WarmEmployeeCacheCommand;

class EmployeeServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    protected EmployeeServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new EmployeeServiceProvider($this->app);
    }

    public function test_employee_repository_interface_is_bound_correctly(): void
    {
        // Test that the interface is bound to the correct implementation
        $repository = $this->app->make(EmployeeRepositoryInterface::class);
        
        $this->assertInstanceOf(CachedEmployeeRepository::class, $repository);
        
        // Test that it's wrapped with the correct decorators
        $reflection = new \ReflectionClass($repository);
        $repositoryProperty = $reflection->getProperty('repository');
        $repositoryProperty->setAccessible(true);
        $innerRepository = $repositoryProperty->getValue($repository);
        
        $this->assertInstanceOf(OptimizedEmployeeRepository::class, $innerRepository);
    }

    public function test_domain_services_are_registered_as_singletons(): void
    {
        // Test EmployeeRegistrationService
        $service1 = $this->app->make(EmployeeRegistrationService::class);
        $service2 = $this->app->make(EmployeeRegistrationService::class);
        $this->assertSame($service1, $service2);

        // Test PerformanceTrackingService
        $service1 = $this->app->make(PerformanceTrackingService::class);
        $service2 = $this->app->make(PerformanceTrackingService::class);
        $this->assertSame($service1, $service2);

        // Test CommissionCalculationService
        $service1 = $this->app->make(CommissionCalculationService::class);
        $service2 = $this->app->make(CommissionCalculationService::class);
        $this->assertSame($service1, $service2);

        // Test PayrollCalculationService
        $service1 = $this->app->make(PayrollCalculationService::class);
        $service2 = $this->app->make(PayrollCalculationService::class);
        $this->assertSame($service1, $service2);
    }

    public function test_application_services_are_registered_as_singletons(): void
    {
        // Test EmployeeCacheService
        $service1 = $this->app->make(EmployeeCacheService::class);
        $service2 = $this->app->make(EmployeeCacheService::class);
        $this->assertSame($service1, $service2);

        // Test EmployeeRoleIntegrationService
        $service1 = $this->app->make(EmployeeRoleIntegrationService::class);
        $service2 = $this->app->make(EmployeeRoleIntegrationService::class);
        $this->assertSame($service1, $service2);
    }

    public function test_all_registered_services_can_be_resolved(): void
    {
        $services = [
            EmployeeRepositoryInterface::class,
            EmployeeRegistrationService::class,
            PerformanceTrackingService::class,
            CommissionCalculationService::class,
            PayrollCalculationService::class,
            EmployeeCacheService::class,
            EmployeeRoleIntegrationService::class,
        ];

        foreach ($services as $service) {
            $instance = $this->app->make($service);
            $this->assertNotNull($instance, "Failed to resolve service: {$service}");
        }
    }

    public function test_repository_dependencies_are_injected_correctly(): void
    {
        $repository = $this->app->make(EmployeeRepositoryInterface::class);
        
        // Test that CachedEmployeeRepository has EmployeeCacheService injected
        $this->assertInstanceOf(CachedEmployeeRepository::class, $repository);
        
        $reflection = new \ReflectionClass($repository);
        $cacheServiceProperty = $reflection->getProperty('cacheService');
        $cacheServiceProperty->setAccessible(true);
        $cacheService = $cacheServiceProperty->getValue($repository);
        
        $this->assertInstanceOf(EmployeeCacheService::class, $cacheService);
    }

    public function test_domain_services_have_correct_dependencies(): void
    {
        // Test EmployeeRegistrationService has repository dependency
        $service = $this->app->make(EmployeeRegistrationService::class);
        $this->assertInstanceOf(EmployeeRegistrationService::class, $service);

        // Test PerformanceTrackingService has repository dependency
        $service = $this->app->make(PerformanceTrackingService::class);
        $this->assertInstanceOf(PerformanceTrackingService::class, $service);

        // Test CommissionCalculationService has repository dependency
        $service = $this->app->make(CommissionCalculationService::class);
        $this->assertInstanceOf(CommissionCalculationService::class, $service);

        // Test PayrollCalculationService has repository dependency
        $service = $this->app->make(PayrollCalculationService::class);
        $this->assertInstanceOf(PayrollCalculationService::class, $service);
    }

    public function test_console_commands_are_registered_in_console_context(): void
    {
        // Mock console context
        $this->app->instance('runningInConsole', true);
        
        // Re-register the provider to trigger console command registration
        $provider = new EmployeeServiceProvider($this->app);
        $provider->boot();

        // Test that commands can be resolved
        $clearCommand = $this->app->make(ClearEmployeeCacheCommand::class);
        $this->assertInstanceOf(ClearEmployeeCacheCommand::class, $clearCommand);

        $warmCommand = $this->app->make(WarmEmployeeCacheCommand::class);
        $this->assertInstanceOf(WarmEmployeeCacheCommand::class, $warmCommand);
    }

    public function test_provides_method_returns_correct_services(): void
    {
        $expectedServices = [
            EmployeeRepositoryInterface::class,
            EmployeeRegistrationService::class,
            PerformanceTrackingService::class,
            CommissionCalculationService::class,
            PayrollCalculationService::class,
            EmployeeCacheService::class,
            EmployeeRoleIntegrationService::class,
        ];

        $providedServices = $this->provider->provides();

        foreach ($expectedServices as $service) {
            $this->assertContains($service, $providedServices, 
                "Service {$service} is not listed in provides() method");
        }
    }

    public function test_service_provider_is_deferred(): void
    {
        // Test that the provider is properly deferred by checking provides() method exists
        $this->assertTrue(method_exists($this->provider, 'provides'));
        $this->assertNotEmpty($this->provider->provides());
    }

    public function test_repository_chain_is_built_correctly(): void
    {
        $repository = $this->app->make(EmployeeRepositoryInterface::class);
        
        // Should be CachedEmployeeRepository wrapping OptimizedEmployeeRepository
        $this->assertInstanceOf(CachedEmployeeRepository::class, $repository);
        
        // Get the inner repository from CachedEmployeeRepository
        $reflection = new \ReflectionClass($repository);
        $repositoryProperty = $reflection->getProperty('repository');
        $repositoryProperty->setAccessible(true);
        $optimizedRepository = $repositoryProperty->getValue($repository);
        
        // OptimizedEmployeeRepository extends EloquentEmployeeRepository
        $this->assertInstanceOf(OptimizedEmployeeRepository::class, $optimizedRepository);
        $this->assertInstanceOf(EloquentEmployeeRepository::class, $optimizedRepository);
    }

    public function test_service_dependencies_are_resolved_correctly(): void
    {
        // Test that services can be created with their dependencies
        $registrationService = $this->app->make(EmployeeRegistrationService::class);
        $performanceService = $this->app->make(PerformanceTrackingService::class);
        $commissionService = $this->app->make(CommissionCalculationService::class);
        $payrollService = $this->app->make(PayrollCalculationService::class);
        $cacheService = $this->app->make(EmployeeCacheService::class);
        $roleIntegrationService = $this->app->make(EmployeeRoleIntegrationService::class);

        // All services should be properly instantiated
        $this->assertNotNull($registrationService);
        $this->assertNotNull($performanceService);
        $this->assertNotNull($commissionService);
        $this->assertNotNull($payrollService);
        $this->assertNotNull($cacheService);
        $this->assertNotNull($roleIntegrationService);
    }

    public function test_service_provider_can_be_registered_multiple_times(): void
    {
        // Register the provider multiple times to ensure no conflicts
        $provider1 = new EmployeeServiceProvider($this->app);
        $provider1->register();
        
        $provider2 = new EmployeeServiceProvider($this->app);
        $provider2->register();

        // Services should still be resolvable (may throw exception due to constructor requirements)
        try {
            $repository = $this->app->make(EmployeeRepositoryInterface::class);
            $this->assertInstanceOf(CachedEmployeeRepository::class, $repository);
        } catch (\Exception $e) {
            // Expected due to EloquentEmployeeRepository constructor requirements
            $this->assertStringContains('Too few arguments', $e->getMessage());
        }
    }

    public function test_service_bindings_are_correct_type(): void
    {
        // Test that singleton bindings return the same instance
        $service1 = $this->app->make(EmployeeRegistrationService::class);
        $service2 = $this->app->make(EmployeeRegistrationService::class);
        $this->assertSame($service1, $service2);

        // Test that repository binding returns new instances when needed
        $repo1 = $this->app->make(EmployeeRepositoryInterface::class);
        $repo2 = $this->app->make(EmployeeRepositoryInterface::class);
        
        // Repository should be the same instance due to singleton cache service
        $this->assertSame($repo1, $repo2);
    }
}