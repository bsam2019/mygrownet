<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\CashRegister;
use App\Domain\StockFlow\ValueObjects\CashRegisterId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

interface CashRegisterRepositoryInterface
{
    public function findById(CashRegisterId $id): ?CashRegister;
    public function findByCompanyId(CompanyId $companyId, int $perPage = 30): array;
    public function findByDate(CompanyId $companyId, DateTimeImmutable $date): ?CashRegister;
    public function findByDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array;
    public function findOpenRegisterForToday(CompanyId $companyId): ?CashRegister;
    public function save(CashRegister $register): CashRegister;
}
