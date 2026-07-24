<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\InvoiceItem;

interface InvoiceItemRepositoryInterface
{
    public function findById(int $id): ?InvoiceItem;

    public function save(InvoiceItem $item): InvoiceItem;

    public function findByInvoice(int $invoiceId): array;

    public function deleteByInvoice(int $invoiceId): void;
}
