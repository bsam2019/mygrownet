<?php

namespace App\Domain\PlatformPayments\Services;

use App\Domain\PlatformPayments\Contracts\PaymentGatewayInterface;
use App\Domain\PlatformPayments\Enums\GatewayProvider;
use App\Domain\PlatformPayments\Gateways\PawapayGateway;
use App\Domain\PlatformPayments\Gateways\MtnMomoGateway;
use App\Domain\PlatformPayments\Gateways\AirtelMoneyGateway;
use App\Domain\PlatformPayments\Gateways\DpoGateway;
use App\Domain\PlatformPayments\Gateways\FlutterwaveGateway;
use App\Domain\PlatformPayments\Gateways\MoneyUnifyGateway;
use App\Domain\PlatformPayments\Gateways\ZamtelKwachaGateway;

class PaymentGatewayFactory
{
    /**
     * Create a payment gateway instance
     */
    public static function create(
        GatewayProvider $gateway,
        array $credentials,
        bool $testMode = false
    ): PaymentGatewayInterface {
        return match($gateway) {
            GatewayProvider::PAWAPAY => new PawapayGateway($credentials, $testMode),
            GatewayProvider::MTN_MOMO => new MtnMomoGateway($credentials, $testMode),
            GatewayProvider::AIRTEL_MONEY => new AirtelMoneyGateway($credentials, $testMode),
            GatewayProvider::DPO => new DpoGateway($credentials, $testMode),
            GatewayProvider::FLUTTERWAVE => new FlutterwaveGateway($credentials, $testMode),
            GatewayProvider::MONEY_UNIFY => new MoneyUnifyGateway($credentials, $testMode),
            GatewayProvider::ZAMTEL_KWACHA => new ZamtelKwachaGateway($credentials, $testMode),
        };
    }

    /**
     * Get all available gateways
     */
    public static function getAvailableGateways(): array
    {
        return array_map(
            fn(GatewayProvider $gateway) => [
                'value' => $gateway->value,
                'label' => $gateway->getLabel(),
                'description' => $gateway->getDescription(),
            ],
            GatewayProvider::cases()
        );
    }

    /**
     * Get gateway configuration fields
     */
    public static function getGatewayFields(GatewayProvider $gateway): array
    {
        $instance = self::create($gateway, [], false);
        return $instance->getRequiredFields();
    }
}
