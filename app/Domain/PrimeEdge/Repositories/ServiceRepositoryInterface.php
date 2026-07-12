<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Service;
use App\Domain\PrimeEdge\ValueObjects\ServiceId;
use App\Domain\PrimeEdge\ValueObjects\ServiceCategory;

interface ServiceRepositoryInterface
{
    public function save(Service $service): void;
    public function findById(ServiceId $id): ?Service;
    public function findAll(): array;
    public function findByCategory(ServiceCategory $category): array;
    public function findActive(): array;
    public function nextId(): ServiceId;
}
