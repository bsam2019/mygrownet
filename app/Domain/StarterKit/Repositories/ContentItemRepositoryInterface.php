<?php

namespace App\Domain\StarterKit\Repositories;

use App\Domain\StarterKit\Entities\ContentItem;

interface ContentItemRepositoryInterface
{
    public function findById(int $id): ?ContentItem;
    
    public function findAll(): array;
    
    public function findActive(): array;
    
    public function findByCategory(string $category): array;
    
    public function save(ContentItem $item): void;
    
    public function delete(int $id): void;
    
    public function getTotalCount(): int;
    
    public function getTotalValue(): int;
}
