<?php

namespace App\Providers;

use App\Domain\Messaging\Repositories\MessageRepository;
use App\Infrastructure\Persistence\Eloquent\Messaging\EloquentMessageRepository;
use Illuminate\Support\ServiceProvider;

class MessagingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            MessageRepository::class,
            EloquentMessageRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
