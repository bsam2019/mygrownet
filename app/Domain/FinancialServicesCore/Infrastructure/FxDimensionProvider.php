<?php

namespace App\Domain\FinancialServicesCore\Infrastructure;

use App\Domain\Core\Contracts\DimensionProvider;

class FxDimensionProvider implements DimensionProvider
{
    public function capability(): string
    {
        return 'fx_dimensions';
    }

    public function getDimensions(): array
    {
        return [
            ['name' => 'base_currency', 'type' => 'string', 'values' => ['ZMW', 'USD', 'ZAR', 'GBP', 'EUR']],
            ['name' => 'rate_source', 'type' => 'string', 'values' => ['boz', 'exchangerate_host', 'manual']],
            ['name' => 'conversion_direction', 'type' => 'string', 'values' => ['direct', 'inverse']],
        ];
    }

    public function resolveLabels(array $dimensionIds): array
    {
        $dimensions = $this->getDimensions();
        $result = [];

        foreach ($dimensions as $dim) {
            $name = $dim['name'];
            if (isset($dimensionIds[$name])) {
                $id = $dimensionIds[$name];
                $result[$name] = $id;
            }
        }

        return $result;
    }
}
