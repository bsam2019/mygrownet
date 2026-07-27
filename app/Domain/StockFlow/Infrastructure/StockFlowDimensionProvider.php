<?php

namespace App\Domain\StockFlow\Infrastructure;

use App\Domain\Core\Contracts\DimensionProvider;
use App\Domain\StockFlow\Repositories\BranchRepositoryInterface;

class StockFlowDimensionProvider implements DimensionProvider
{
    public function __construct(
        private BranchRepositoryInterface $branchRepository,
    ) {}

    public function capability(): string
    {
        return 'stockflow_dimensions';
    }

    public function getDimensions(): array
    {
        return [
            ['name' => 'branch', 'type' => 'string', 'values' => []],
            ['name' => 'warehouse', 'type' => 'string', 'values' => []],
        ];
    }

    public function resolveLabels(array $dimensionIds): array
    {
        $result = [];

        if (isset($dimensionIds['branch'])) {
            $branchIds = (array) $dimensionIds['branch'];
            $labels = [];
            foreach ($branchIds as $id) {
                $branch = $this->branchRepository->findById((int) $id);
                if ($branch) {
                    $labels[$id] = $branch->getName();
                }
            }
            $result['branch'] = $labels;
        }

        return $result;
    }
}
