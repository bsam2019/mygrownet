<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Employee\Repositories\CalendarEventRepositoryInterface;
use App\Domain\Employee\Repositories\DelegationRepositoryInterface;
use App\Domain\Employee\Repositories\DepartmentRepositoryInterface;
use App\Domain\Employee\Repositories\EmployeeCommissionRepositoryInterface;
use App\Domain\Employee\Repositories\EmployeePerformanceRepositoryInterface;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\Repositories\ExpenseRepositoryInterface;
use App\Domain\Employee\Repositories\NotificationRepositoryInterface;
use App\Domain\Employee\Repositories\SupportTicketRepositoryInterface;
use App\Domain\Employee\Repositories\TrainingRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentCalendarEventRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentDelegationRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentDepartmentRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentEmployeeCommissionRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentEmployeePerformanceRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentExpenseRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentNotificationRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentPositionRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentSupportTicketRepository;
use App\Infrastructure\Persistence\Repositories\Employee\EloquentTrainingRepository;

class EmployeeDomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, EloquentDepartmentRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, EloquentPositionRepository::class);
        $this->app->bind(EmployeeCommissionRepositoryInterface::class, EloquentEmployeeCommissionRepository::class);
        $this->app->bind(EmployeePerformanceRepositoryInterface::class, EloquentEmployeePerformanceRepository::class);
        $this->app->bind(CalendarEventRepositoryInterface::class, EloquentCalendarEventRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, EloquentExpenseRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, EloquentNotificationRepository::class);
        $this->app->bind(SupportTicketRepositoryInterface::class, EloquentSupportTicketRepository::class);
        $this->app->bind(TrainingRepositoryInterface::class, EloquentTrainingRepository::class);
        $this->app->bind(DelegationRepositoryInterface::class, EloquentDelegationRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/employee'));
    }
}
