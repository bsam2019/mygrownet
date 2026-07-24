<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Quotation;

interface QuotationRepositoryInterface
{
    public function findById(int $id): ?Quotation;

    public function save(Quotation $quotation): Quotation;

    public function findByBusiness(int $businessId): array;

    public function findByCustomer(int $customerId): array;

    public function findByStatus(int $businessId, string $status): array;

    public function findPending(int $businessId): array;

    public function findExpired(int $businessId): array;
}
