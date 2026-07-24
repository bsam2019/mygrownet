<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\QuotationItem;

interface QuotationItemRepositoryInterface
{
    public function findById(int $id): ?QuotationItem;

    public function save(QuotationItem $quotationItem): QuotationItem;

    public function findByQuotation(int $quotationId): array;
}
