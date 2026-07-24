<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Invoice;

interface InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice;

    public function save(Invoice $invoice): Invoice;

    public function findByBusiness(int $businessId): array;

    public function findByCustomer(int $customerId): array;

    public function findByStatus(int $businessId, string $status): array;

    public function findOverdue(int $businessId): array;

    public function findByNumber(int $businessId, string $number): ?Invoice;
}
