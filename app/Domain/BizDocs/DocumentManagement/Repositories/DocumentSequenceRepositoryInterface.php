<?php

namespace App\Domain\BizDocs\DocumentManagement\Repositories;

interface DocumentSequenceRepositoryInterface
{
    public function lockForUpdate(int $businessId, string $documentType, int $year): ?array;

    public function create(int $businessId, string $documentType, int $year, string $prefix, int $padding, bool $includeYear): array;

    public function find(int $businessId, string $documentType, int $year): ?array;

    public function updateNextNumber(int $id, int $nextNumber): void;

    public function updateOrCreate(array $attributes, array $values): void;
}