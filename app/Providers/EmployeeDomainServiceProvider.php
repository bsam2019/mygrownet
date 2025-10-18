<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Application\UseCases\Employee\CreateEmployeeUseCase;
use App\Application\UseCases\Employee\UpdateEmployeeUseCase;
use App\Application\UseCases\Employee\GetEmployeeUseCase;
use App\Application\UseCases\Employee\DeleteEmployeeUseCase;
use App\Domain\Employee\Services\EmployeeRegistrationService;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;

class EmployeeDomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            EmployeeRepositoryInterface::class,
            EloquentEmployeeRepository::class
        );

        // Register Use Cases
        $this->app->bind(CreateEmployeeUseCase::class, function ($app) {
            return new CreateEmployeeUseCase(
                $app->make(EmployeeRepositoryInterface::class),
                $app->make(EmployeeRegistrationService::class)
            );
        });

        $this->app->bind(UpdateEmployeeUseCase::class, function ($app) {
            return new UpdateEmployeeUseCase(
                $app->make(EmployeeRepositoryInterface::class)
            );
        });

        $this->app->bind(GetEmployeeUseCase::class, function ($app) {
            return new GetEmployeeUseCase(
                $app->make(EmployeeRepositoryInterface::class)
            );
        });

        $this->app->bind(DeleteEmployeeUseCase::class, function ($app) {
            return new DeleteEmployeeUseCase(
                $app->make(EmployeeRepositoryInterface::class)
            );
        });

        // Register domain services
        $this->app->bind(EmployeeRegistrationService::class, function ($app) {
            return new EmployeeRegistrationService(
                $app->make(EmployeeRepositoryInterface::class)
            );
        });

        // Register Eloquent model for repository
        $this->app->bind(EmployeeModel::class, function () {
            return new EmployeeModel();
        });
    }

    public function boot(): void
    {
        // Register event listeners if needed
        // Event::listen(EmployeeHired::class, EmployeeHiredListener::class);
        // Event::listen(EmployeeUpdated::class, EmployeeUpdatedListener::class);
        // Event::listen(EmployeeTerminated::class, EmployeeTerminatedListener::class);
    }
}