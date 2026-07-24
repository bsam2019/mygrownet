<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\ScenarioModel;

interface ScenarioModelRepositoryInterface
{
    public function findActive(): array;
}
