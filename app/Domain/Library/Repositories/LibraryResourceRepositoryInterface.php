<?php

namespace App\Domain\Library\Repositories;

use App\Domain\Library\Entities\LibraryResource;
use App\Domain\Library\ValueObjects\ResourceType;
use App\Domain\Library\ValueObjects\ResourceCategory;

interface LibraryResourceRepositoryInterface
{
    public function findById(int $id): ?LibraryResource;
    
    public function findAll(): array;
    
    public function findActive(): array;
    
    public function findFeatured(int $limit = 6): array;
    
    public function findByCategory(ResourceCategory $category): array;
    
    public function findByType(ResourceType $type): array;
    
    public function save(LibraryResource $resource): void;
    
    public function delete(int $id): void;
    
    public function getTotalCount(): int;
    
    public function getActiveCount(): int;
}
