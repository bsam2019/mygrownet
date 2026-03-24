<?php

namespace App\Domain\BizDocs\DocumentManagement\Repositories;

use App\Domain\BizDocs\DocumentManagement\Entities\Document;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentNumber;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;

interface DocumentRepositoryInterface
{
    public function save(Document $document): Document;

    public function findById(int $id): ?Document;

    public function findByNumber(int $businessId, DocumentNumber $number): ?Document;

    public function findByBusiness(int $businessId, ?DocumentType $type = null, int $page = 1, int $perPage = 20): array;

    public function findByCustomer(int $customerId, int $page = 1, int $perPage = 20): array;

    public function delete(int $id): bool;

    public function countByBusiness(int $businessId, ?DocumentType $type = null): int;
}
