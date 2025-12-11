<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Repositories;

use App\Domain\GrowStart\Entities\Stage;
use Illuminate\Support\Collection;

interface StageRepositoryInterface
{
    public function findById(int $id): ?Stage;
    
    public function findBySlug(string $slug): ?Stage;
    
    public function findAll(): Collection;
    
    public function findActive(): Collection;
    
    public function findFirst(): ?Stage;
    
    public function findNext(int $currentStageId): ?Stage;
    
    public function findPrevious(int $currentStageId): ?Stage;
}
