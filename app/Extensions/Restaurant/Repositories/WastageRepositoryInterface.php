<?php

declare(strict_types=1);

namespace App\Extensions\Restaurant\Repositories;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Extensions\Restaurant\Entities\WastageRecord;
use App\Extensions\Restaurant\ValueObjects\WastageRecordId;

interface WastageRepositoryInterface
{
    public function findById(WastageRecordId $id): ?WastageRecord;
    public function findByCompany(CompanyId $companyId): array;
    public function save(WastageRecord $record): WastageRecord;
    public function delete(WastageRecordId $id): void;
}
