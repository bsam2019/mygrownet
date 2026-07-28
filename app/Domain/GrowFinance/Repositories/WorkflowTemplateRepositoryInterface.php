<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\WorkflowTemplate;

interface WorkflowTemplateRepositoryInterface
{
    public function findById(int $id): ?WorkflowTemplate;

    public function findByBusiness(int $businessId): array;

    public function findByEntityType(int $businessId, string $entityType): array;

    public function findActiveByEntityType(int $businessId, string $entityType): ?WorkflowTemplate;

    public function save(WorkflowTemplate $template): WorkflowTemplate;

    public function delete(int $id): void;
}
