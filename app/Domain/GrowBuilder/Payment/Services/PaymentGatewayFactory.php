<?php

namespace App\Domain\GrowBuilder\Payment\Services;

use App\Domain\GrowBuilder\Payment\Contracts\PaymentGatewayInterface;
use App\Domain\GrowBuilder\Payment\Enums\PaymentGateway;
use App\Domain\GrowBuilder\Payment\Gateways\PawapayGateway;
use App\Domain\GrowBuilder\Payment\Gateways\FlutterwaveGateway;
use App\Domain\GrowBuilder\Payment\Gateways\DpoGateway;

class PaymentGatewayFactory
{
    /**
     * Create a payment gateway instance
     */
    public static function create(
        PaymentGateway $gateway,
        array $credentials,
        bool $testMode = false
    ): PaymentGatewayInterface {
        return match($gateway) {
            PaymentGateway::PAWAPAY => new PawapayGateway($credentials, $testMode),
            PaymentGateway::FLUTTERWAVE => new FlutterwaveGateway($credentials, $testMode),
            PaymentGateway::DPO => new DpoGateway($credentials, $testMode),
        };
    }

    /**
     * Get all available gateways
     */
    public static function getAvailableGateways(): array
    {
        return array_map(
            fn(PaymentGateway $gateway) => [
                'value' => $gateway->value,
                'label' => $gateway->getLabel(),
                'description' => $gateway->getDescription(),
            ],
            PaymentGateway::cases()
        );
    }

    /**
     * Get gateway configuration fields
     */
    public static function getGatewayFields(PaymentGateway $gateway): array
    {
        $instance = self::create($gateway, [], false);
        return $instance->getRequiredFields();
    }
}
