<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Repositories;

use App\Domain\QuickInvoice\Entities\Template;

interface TemplateRepositoryInterface
{
    public function findById(int $id): ?Template;

    public function findByUser(int $userId): array;

    public function findAll(): array;

    public function save(Template $template): Template;

    public function delete(int $id): bool;

    public function replicate(int $id, int $newUserId, string $newName): ?Template;
}