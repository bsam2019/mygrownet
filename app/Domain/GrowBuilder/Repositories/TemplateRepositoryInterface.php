<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Entities\Template;
use App\Domain\GrowBuilder\ValueObjects\TemplateId;
use App\Domain\GrowBuilder\ValueObjects\TemplateCategory;

interface TemplateRepositoryInterface
{
    public function findById(TemplateId $id): ?Template;
    public function findBySlug(string $slug): ?Template;
    public function findAll(): array;
    public function findActive(): array;
    public function findByCategory(TemplateCategory $category): array;
    public function findFree(): array;
    public function findPremium(): array;
    public function save(Template $template): Template;
    public function delete(TemplateId $id): void;
}
