<?php

declare(strict_types=1);

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\DTOs\CollectionResponse;
use App\Domain\Payment\DTOs\DisbursementRequest;
use App\Domain\Payment\DTOs\DisbursementResponse;
use App\Domain\Payment\Enums\TransactionStatus;

interface PaymentGatewayInterface
{
    /**
     * Get the gateway identifier
     */
    public function getIdentifier(): string;

    /**
     * Get the gateway display name
     */
    public function getName(): string;

    /**
     * Get supported countries (ISO 3166-1 alpha-2 codes)
     */
    public function getSupportedCountries(): array;

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array;

    /**
     * Check if gateway supports collections (receiving money)
     */
    public function supportsCollections(): bool;

    /**
     * Check if gateway supports disbursements (sending money)
     */
    public function supportsDisbursements(): bool;

    /**
     * Initiate a collection (receive money from customer)
     */
    public function collect(CollectionRequest $request): CollectionResponse;

    /**
     * Initiate a disbursement (send money to recipient)
     */
    public function disburse(DisbursementRequest $request): DisbursementResponse;

    /**
     * Check collection transaction status
     */
    public function checkCollectionStatus(string $transactionId): TransactionStatus;

    /**
     * Check disbursement transaction status
     */
    public function checkDisbursementStatus(string $transactionId): TransactionStatus;

    /**
     * Check if gateway is properly configured
     */
    public function isConfigured(): bool;
}
