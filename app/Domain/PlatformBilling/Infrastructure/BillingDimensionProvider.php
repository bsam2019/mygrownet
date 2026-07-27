<?php

namespace App\Domain\PlatformBilling\Infrastructure;

use App\Domain\Core\Contracts\DimensionProvider;

class BillingDimensionProvider implements DimensionProvider
{
    public function capability(): string
    {
        return 'billing_dimensions';
    }

    public function getDimensions(): array
    {
        return [
            ['name' => 'subscription_status', 'type' => 'string', 'values' => ['active', 'trial', 'expired', 'suspended', 'cancelled']],
            ['name' => 'plan_tier', 'type' => 'string', 'values' => ['free', 'basic', 'pro', 'enterprise']],
            ['name' => 'billing_cycle', 'type' => 'string', 'values' => ['monthly', 'quarterly', 'annual']],
            ['name' => 'payment_method_type', 'type' => 'string', 'values' => ['mobile_money', 'card', 'bank_transfer']],
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
