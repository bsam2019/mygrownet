<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\WhiteLabel;

interface WhiteLabelRepositoryInterface
{
    public function findById(int $id): ?WhiteLabel;

    public function save(WhiteLabel $whiteLabel): WhiteLabel;

    public function findByBusiness(int $businessId): ?WhiteLabel;
}
