<?php

namespace App\Application\PrimeEdge\DTOs;

use App\Domain\PrimeEdge\Entities\Invoice;
use App\Domain\PrimeEdge\Entities\InvoiceLineItem;

class InvoiceDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $clientId,
        public readonly string $number,
        public readonly string $status,
        public readonly array $total,
        public readonly ?string $engagementId,
        public readonly ?string $notes,
        public readonly array $lineItems,
        public readonly string $createdAt,
        public readonly ?string $sentAt,
        public readonly ?string $paidAt,
    ) {}

    public static function fromEntity(Invoice $invoice): self
    {
        return new self(
            id: $invoice->id()->toString(),
            clientId: $invoice->clientId()->toString(),
            number: $invoice->number()->toString(),
            status: $invoice->status()->value,
            total: $invoice->total()->toArray(),
            engagementId: $invoice->engagementId(),
            notes: $invoice->notes(),
            lineItems: array_map(fn(InvoiceLineItem $item) => [
                'description' => $item->description(),
                'unitPrice' => $item->unitPrice()->toArray(),
                'quantity' => $item->quantity(),
                'total' => $item->total()->toArray(),
            ], $invoice->lineItems()),
            createdAt: $invoice->createdAt()->format('Y-m-d H:i:s'),
            sentAt: $invoice->sentAt()?->format('Y-m-d H:i:s'),
            paidAt: $invoice->paidAt()?->format('Y-m-d H:i:s'),
        );
    }
}
