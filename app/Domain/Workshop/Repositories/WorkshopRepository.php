<?php

namespace App\Domain\Workshop\Repositories;

use App\Domain\Workshop\Entities\Workshop;

interface WorkshopRepository
{
    public function save(Workshop $workshop): Workshop;
    
    public function findById(int $id): ?Workshop;
    
    public function findBySlug(string $slug): ?Workshop;
    
    public function findAll(): array;
    
    public function findPublished(): array;
    
    public function findByCategory(string $category): array;
    
    public function findUpcoming(): array;
}
