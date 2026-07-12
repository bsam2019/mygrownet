<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Inquiry;
use App\Domain\PrimeEdge\ValueObjects\InquiryId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;

interface InquiryRepositoryInterface
{
    public function save(Inquiry $inquiry): void;
    public function findById(InquiryId $id): ?Inquiry;
    public function findByClientId(ClientId $clientId): array;
    public function findAll(): array;
    public function findByStatus(string $status): array;
    public function nextId(): InquiryId;
}
