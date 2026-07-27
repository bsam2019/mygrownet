<?php

namespace App\Domain\FinancialServicesCore\Repositories;

use App\Domain\FinancialServicesCore\Entities\Currency;

interface CurrencyRepositoryInterface
{
    public function findByCode(string $code): ?Currency;
    public function findActive(): array;
    public function findAll(): array;
    public function save(Currency $currency): Currency;
}
