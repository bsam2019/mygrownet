<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\QuotationItem;

interface QuotationItemRepositoryInterface
{
    public function findById(int $id): ?QuotationItem;

    public function save(QuotationItem $item): QuotationItem;

    public function findByQuotation(int $quotationId): array;

    public function deleteByQuotation(int $quotationId): void;
}
