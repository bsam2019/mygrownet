<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Interfaces
use App\Domain\Employee\Repositories\TaskRepositoryInterface;
use App\Domain\Employee\Repositories\TimeOffRepositoryInterface;
use App\Domain\Employee\Repositories\GoalRepositoryInterface;
use App\Domain\Employee\Repositories\AttendanceRepositoryInterface;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;

// Repository Implementations
use App\Infrastructure\Persistence\Repositories\EloquentTaskRepository;
use App\Infrastructure\Persistence\Repositories\EloquentTimeOffRepository;
use App\Infrastructure\Persistence\Repositories\EloquentGoalRepository;
use App\Infrastructure\Persistence\Repositories\EloquentAttendanceRepository;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;

// Domain Services
use App\Domain\Employee\Services\TaskManagementService;
use App\Domain\Employee\Services\TimeOffService;
use App\Domain\Employee\Services\GoalTrackingService;
use App\Domain\Employee\Services\AttendanceService;
use App\Domain\Employee\Services\EmployeePortalService;

class EmployeePortalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Interfaces to Implementations
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
        $this->app->bind(TimeOffRepositoryInterface::class, EloquentTimeOffRepository::class);
        $this->app->bind(GoalRepositoryInterface::class, EloquentGoalRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, EloquentAttendanceRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);

        // Register Domain Services as Singletons
        $this->app->singleton(TaskManagementService::class, function ($app) {
            return new TaskManagementService(
                $app->make(TaskRepositoryInterface::class)
            );
        });

        $this->app->singleton(TimeOffService::class, function ($app) {
            return new TimeOffService(
                $app->make(TimeOffRepositoryInterface::class)
            );
        });

        $this->app->singleton(GoalTrackingService::class, function ($app) {
            return new GoalTrackingService(
                $app->make(GoalRepositoryInterface::class)
            );
        });

        $this->app->singleton(AttendanceService::class, function ($app) {
            return new AttendanceService(
                $app->make(AttendanceRepositoryInterface::class)
            );
        });

        // EmployeePortalService is optional - controller uses individual services directly
        // $this->app->singleton(EmployeePortalService::class, function ($app) {
        //     return new EmployeePortalService(
        //         $app->make(EmployeeRepositoryInterface::class),
        //         $app->make(TaskManagementService::class),
        //         $app->make(GoalTrackingService::class),
        //         $app->make(TimeOffService::class),
        //         $app->make(AttendanceService::class)
        //     );
        // });
    }

    public function boot(): void
    {
        //
    }
}
