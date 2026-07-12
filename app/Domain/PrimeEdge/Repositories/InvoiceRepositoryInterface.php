<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Invoice;
use App\Domain\PrimeEdge\ValueObjects\InvoiceId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;

interface InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void;
    public function findById(InvoiceId $id): ?Invoice;
    public function findByClientId(ClientId $clientId): array;
    public function findOverdue(): array;
    public function findAll(): array;
    public function nextId(): InvoiceId;
    public function nextSequenceNumber(int $year): int;
}
