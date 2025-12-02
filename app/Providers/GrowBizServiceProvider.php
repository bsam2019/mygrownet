<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\GrowBiz\Repositories\TaskRepositoryInterface;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\GrowBizTaskRepository;
use App\Infrastructure\Persistence\Repositories\GrowBizEmployeeRepository;
use App\Domain\GrowBiz\Services\TaskManagementService;
use App\Domain\GrowBiz\Services\EmployeeManagementService;
use App\Domain\GrowBiz\Services\NotificationService;
use App\Domain\GrowBiz\Services\AnalyticsService;
use App\Domain\GrowBiz\Services\SummaryService;
use App\Domain\GrowBiz\Services\ExportService;
use App\Domain\GrowBiz\Services\EmployeeInvitationService;

class GrowBizServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            TaskRepositoryInterface::class,
            GrowBizTaskRepository::class
        );

        $this->app->bind(
            EmployeeRepositoryInterface::class,
            GrowBizEmployeeRepository::class
        );

        // Register notification service
        $this->app->singleton(NotificationService::class);

        // Register domain services
        $this->app->singleton(TaskManagementService::class, function ($app) {
            return new TaskManagementService(
                $app->make(TaskRepositoryInterface::class),
                $app->make(EmployeeRepositoryInterface::class),
                $app->make(NotificationService::class)
            );
        });

        $this->app->singleton(EmployeeManagementService::class, function ($app) {
            return new EmployeeManagementService(
                $app->make(EmployeeRepositoryInterface::class),
                $app->make(TaskRepositoryInterface::class)
            );
        });

        // Register analytics service
        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService(
                $app->make(TaskRepositoryInterface::class),
                $app->make(EmployeeRepositoryInterface::class)
            );
        });

        // Register summary service
        $this->app->singleton(SummaryService::class, function ($app) {
            return new SummaryService(
                $app->make(TaskRepositoryInterface::class),
                $app->make(EmployeeRepositoryInterface::class)
            );
        });

        // Register export service
        $this->app->singleton(ExportService::class, function ($app) {
            return new ExportService(
                $app->make(TaskRepositoryInterface::class),
                $app->make(EmployeeRepositoryInterface::class),
                $app->make(AnalyticsService::class),
                $app->make(SummaryService::class)
            );
        });

        // Register employee invitation service
        $this->app->singleton(EmployeeInvitationService::class, function ($app) {
            return new EmployeeInvitationService(
                $app->make(EmployeeRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
