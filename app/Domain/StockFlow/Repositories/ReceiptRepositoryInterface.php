<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Receipt;
use App\Domain\StockFlow\ValueObjects\ReceiptId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

interface ReceiptRepositoryInterface
{
    public function findById(ReceiptId $id): ?Receipt;
    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array;
    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array;
    public function findBySaleId(CompanyId $companyId, int $saleId): array;
    public function save(Receipt $receipt): Receipt;
    public function nextReceiptNumber(): string;
    public function getMaxId(): int;
}
