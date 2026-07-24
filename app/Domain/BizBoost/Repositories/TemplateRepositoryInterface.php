<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Template;

interface TemplateRepositoryInterface
{
    public function findById(int $id): ?Template;

    public function findActive(array $filters = []): array;

    public function save(Template $entity): Template;

    public function incrementUsage(int $id): void;
}