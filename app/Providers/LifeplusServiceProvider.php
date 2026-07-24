<?php

namespace App\Providers;

use App\Domain\LifePlus\Repositories\BudgetRepositoryInterface;
use App\Domain\LifePlus\Repositories\CommunityPostRepositoryInterface;
use App\Domain\LifePlus\Repositories\ExpenseRepositoryInterface;
use App\Domain\LifePlus\Repositories\GigRepositoryInterface;
use App\Domain\LifePlus\Repositories\HabitRepositoryInterface;
use App\Domain\LifePlus\Repositories\NoteRepositoryInterface;
use App\Domain\LifePlus\Repositories\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\LifePlus\EloquentBudgetRepository;
use App\Infrastructure\Persistence\Repositories\LifePlus\EloquentCommunityPostRepository;
use App\Infrastructure\Persistence\Repositories\LifePlus\EloquentExpenseRepository;
use App\Infrastructure\Persistence\Repositories\LifePlus\EloquentGigRepository;
use App\Infrastructure\Persistence\Repositories\LifePlus\EloquentHabitRepository;
use App\Infrastructure\Persistence\Repositories\LifePlus\EloquentNoteRepository;
use App\Infrastructure\Persistence\Repositories\LifePlus\EloquentTaskRepository;
use Illuminate\Support\ServiceProvider;

class LifeplusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, EloquentExpenseRepository::class);
        $this->app->bind(BudgetRepositoryInterface::class, EloquentBudgetRepository::class);
        $this->app->bind(HabitRepositoryInterface::class, EloquentHabitRepository::class);
        $this->app->bind(NoteRepositoryInterface::class, EloquentNoteRepository::class);
        $this->app->bind(GigRepositoryInterface::class, EloquentGigRepository::class);
        $this->app->bind(CommunityPostRepositoryInterface::class, EloquentCommunityPostRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/lifeplus'));
    }
}
