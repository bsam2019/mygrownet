<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\InvoiceTemplate;

interface InvoiceTemplateRepositoryInterface
{
    public function findById(int $id): ?InvoiceTemplate;

    public function save(InvoiceTemplate $invoiceTemplate): InvoiceTemplate;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findDefault(int $businessId): ?InvoiceTemplate;
}
