<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use App\Domain\Core\Contracts\DimensionProvider;

class PaymentDimensionProvider implements DimensionProvider
{
    public function capability(): string
    {
        return 'payment_dimensions';
    }

    public function getDimensions(): array
    {
        return [
            ['name' => 'payment_method', 'type' => 'string', 'values' => ['mtn_momo', 'airtel_money', 'moneyunify', 'card', 'bank_transfer']],
            ['name' => 'transaction_status', 'type' => 'string', 'values' => ['initiated', 'pending', 'completed', 'failed', 'refunded', 'settled', 'reconciled']],
            ['name' => 'provider', 'type' => 'string', 'values' => ['mtn', 'airtel', 'moneyunify', 'stripe', 'paypal']],
            ['name' => 'settlement_status', 'type' => 'string', 'values' => ['matched', 'discrepancy', 'reconciled']],
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
