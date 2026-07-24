<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TransactionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/transaction'));
    }
}
