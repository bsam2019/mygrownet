<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\ReferralPartner;
use App\Domain\PrimeEdge\ValueObjects\PartnerId;

interface ReferralPartnerRepositoryInterface
{
    public function save(ReferralPartner $partner): void;
    public function findById(PartnerId $id): ?ReferralPartner;
    public function findAll(): array;
    public function findByType(string $type): array;
    public function findActive(): array;
    public function nextId(): PartnerId;
}
