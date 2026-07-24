<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\InvoiceItem;

interface InvoiceItemRepositoryInterface
{
    public function findById(int $id): ?InvoiceItem;

    public function save(InvoiceItem $invoiceItem): InvoiceItem;

    public function findByInvoice(int $invoiceId): array;
}
