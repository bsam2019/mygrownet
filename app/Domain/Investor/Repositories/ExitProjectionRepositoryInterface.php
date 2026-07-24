<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\ExitProjection;

interface ExitProjectionRepositoryInterface
{
    public function findAll(): array;
}
