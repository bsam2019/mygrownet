<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Transfer;
use App\Domain\StockFlow\ValueObjects\TransferId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface TransferRepositoryInterface
{
    public function findById(TransferId $id): ?Transfer;
    public function findByCompanyId(CompanyId $companyId, int $perPage = 20): array;
    public function findByStatus(CompanyId $companyId, string $status): array;
    public function save(Transfer $transfer): Transfer;
    public function nextTransferNumber(): string;
}
