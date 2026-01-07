<?php

namespace App\Domain\Wedding\Repositories;

use App\Domain\Wedding\Entities\WeddingTemplate;

interface WeddingTemplateRepositoryInterface
{
    public function findById(int $id): ?WeddingTemplate;
    
    public function findBySlug(string $slug): ?WeddingTemplate;
    
    public function findAll(): array;
    
    public function findActive(): array;
    
    public function save(WeddingTemplate $template): WeddingTemplate;
    
    public function delete(int $id): bool;
}
