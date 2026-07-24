<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Quotation;

interface QuotationRepositoryInterface
{
    public function findById(int $id): ?Quotation;

    public function save(Quotation $quotation): Quotation;

    public function findByCompany(int $companyId): array;

    public function findByCustomer(int $customerId): array;

    public function findByStatus(int $companyId, string $status): array;

    public function findByNumber(int $companyId, string $number): ?Quotation;

    public function getSummary(int $companyId): array;
}
