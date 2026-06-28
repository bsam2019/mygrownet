<?php

namespace App\Domain\Wedding\Repositories;

use App\Domain\Wedding\Entities\WeddingEvent;
use App\Domain\Wedding\ValueObjects\WeddingStatus;
use Carbon\Carbon;

interface WeddingEventRepositoryInterface
{
    public function save(WeddingEvent $weddingEvent): WeddingEvent;
    
    public function findById(int $id): ?WeddingEvent;
    
    public function findByUserId(int $userId): array;
    
    public function findByStatus(WeddingStatus $status): array;
    
    public function findUpcomingEvents(int $limit = 10): array;
    
    public function findEventsByDateRange(Carbon $startDate, Carbon $endDate): array;
    
    public function findUserActiveEvent(int $userId): ?WeddingEvent;
    
    public function findBySlug(string $slug): ?WeddingEvent;
    
    public function delete(int $id): bool;
    
    public function getEventStats(): array;
}