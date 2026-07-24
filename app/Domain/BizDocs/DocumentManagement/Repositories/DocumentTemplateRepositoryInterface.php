<?php

namespace App\Domain\BizDocs\DocumentManagement\Repositories;

interface DocumentTemplateRepositoryInterface
{
    public function findById(int $id);

    public function getAvailableForBusiness(int $businessId): array;

    public function findAll(): array;

    public function getUserTemplatePreference(int $businessId, string $documentType): ?array;

    public function setUserTemplatePreference(int $businessId, string $documentType, int $templateId): void;
}