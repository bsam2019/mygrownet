<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\GrowBiz\Repositories\TaskRepositoryInterface;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use App\Domain\GrowBiz\Repositories\PersonalTodoRepositoryInterface;
use App\Domain\GrowBiz\Repositories\InventoryRepositoryInterface;
use App\Domain\GrowBiz\Repositories\AppointmentRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\GrowBizTaskRepository;
use App\Infrastructure\Persistence\Repositories\GrowBizEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\EloquentPersonalTodoRepository;
use App\Infrastructure\Persistence\Repositories\EloquentInventoryRepository;
use App\Infrastructure\Persistence\Repositories\EloquentAppointmentRepository;
use App\Domain\GrowBiz\Services\TaskManagementService;
use App\Domain\GrowBiz\Services\EmployeeManagementService;
use App\Domain\GrowBiz\Services\NotificationService;
use App\Domain\GrowBiz\Services\AnalyticsService;
use App\Domain\GrowBiz\Services\SummaryService;
use App\Domain\GrowBiz\Services\ExportService;
use App\Domain\GrowBiz\Services\EmployeeInvitationService;
use App\Domain\GrowBiz\Services\PersonalTodoService;
use App\Domain\GrowBiz\Services\InventoryService;
use App\Domain\GrowBiz\Services\AppointmentService;
use App\Domain\GrowBiz\Services\ProjectService;

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

        $this->app->bind(
            PersonalTodoRepositoryInterface::class,
            EloquentPersonalTodoRepository::class
        );

        $this->app->bind(
            InventoryRepositoryInterface::class,
            EloquentInventoryRepository::class
        );

        $this->app->bind(
            AppointmentRepositoryInterface::class,
            EloquentAppointmentRepository::class
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

        // Register personal todo service
        $this->app->singleton(PersonalTodoService::class, function ($app) {
            return new PersonalTodoService(
                $app->make(PersonalTodoRepositoryInterface::class)
            );
        });

        // Register inventory service
        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService(
                $app->make(InventoryRepositoryInterface::class)
            );
        });

        // Register appointment service
        $this->app->singleton(AppointmentService::class, function ($app) {
            return new AppointmentService(
                $app->make(AppointmentRepositoryInterface::class)
            );
        });

        // Register project service
        $this->app->singleton(ProjectService::class, function ($app) {
            return new ProjectService();
        });
    }

    public function boot(): void
    {
        //
    }
}
