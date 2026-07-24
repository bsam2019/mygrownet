<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Invoice;

interface InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice;

    public function save(Invoice $invoice): Invoice;

    public function findByCompany(int $companyId): array;

    public function findByCustomer(int $customerId): array;

    public function findByStatus(int $companyId, string $status): array;

    public function findOverdue(int $companyId): array;

    public function findByNumber(int $companyId, string $number): ?Invoice;

    public function getSummary(int $companyId): array;

    public function findPendingByCompany(int $companyId): array;
}
