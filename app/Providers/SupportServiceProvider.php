<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\Support\Repositories\TicketCommentRepository;
use App\Infrastructure\Persistence\Eloquent\Support\EloquentTicketRepository;
use App\Infrastructure\Persistence\Eloquent\Support\EloquentTicketCommentRepository;

class SupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);
        $this->app->bind(TicketCommentRepository::class, EloquentTicketCommentRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
