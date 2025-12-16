<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Contracts\PaymentGatewayInterface;
use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\DTOs\CollectionResponse;
use App\Domain\Payment\DTOs\DisbursementRequest;
use App\Domain\Payment\DTOs\DisbursementResponse;
use App\Domain\Payment\Enums\TransactionStatus;
use App\Domain\Payment\Gateways\MoneyUnifyGateway;
use App\Domain\Payment\Gateways\PawapayGateway;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private array $gateways = [];
    private string $defaultGateway;

    public function __construct()
    {
        $this->defaultGateway = config('services.payment.default_gateway', 'moneyunify');
        $this->registerGateways();
    }

    private function registerGateways(): void
    {
        $this->gateways['moneyunify'] = new MoneyUnifyGateway();
        $this->gateways['pawapay'] = new PawapayGateway();
    }

    public function getGateway(?string $identifier = null): PaymentGatewayInterface
    {
        $identifier = $identifier ?? $this->defaultGateway;
        
        if (!isset($this->gateways[$identifier])) {
            throw new \InvalidArgumentException("Payment gateway '{$identifier}' not found");
        }

        return $this->gateways[$identifier];
    }

    public function getAvailableGateways(): array
    {
        return array_map(fn($gateway) => [
            'identifier' => $gateway->getIdentifier(),
            'name' => $gateway->getName(),
            'countries' => $gateway->getSupportedCountries(),
            'currencies' => $gateway->getSupportedCurrencies(),
            'supportsCollections' => $gateway->supportsCollections(),
            'supportsDisbursements' => $gateway->supportsDisbursements(),
        ], $this->gateways);
    }

    public function collect(CollectionRequest $request, ?string $gateway = null): CollectionResponse
    {
        $gatewayInstance = $this->getGateway($gateway);
        
        Log::info('Processing collection', [
            'gateway' => $gatewayInstance->getIdentifier(),
            'amount' => $request->amount,
            'currency' => $request->currency,
        ]);

        return $gatewayInstance->collect($request);
    }

    public function disburse(DisbursementRequest $request, ?string $gateway = null): DisbursementResponse
    {
        $gatewayInstance = $this->getGateway($gateway);
        
        Log::info('Processing disbursement', [
            'gateway' => $gatewayInstance->getIdentifier(),
            'amount' => $request->amount,
            'currency' => $request->currency,
        ]);

        return $gatewayInstance->disburse($request);
    }

    public function checkCollectionStatus(string $transactionId, ?string $gateway = null): TransactionStatus
    {
        return $this->getGateway($gateway)->checkCollectionStatus($transactionId);
    }

    public function checkDisbursementStatus(string $transactionId, ?string $gateway = null): TransactionStatus
    {
        return $this->getGateway($gateway)->checkDisbursementStatus($transactionId);
    }

    public function getDefaultGateway(): string
    {
        return $this->defaultGateway;
    }
}
