<?php

namespace App\Domain\GrowBuilder\Payment\Contracts;

use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;

/**
 * Payment Gateway Interface for GrowBuilder
 * 
 * All payment gateways must implement this interface to ensure
 * consistent behavior across different payment providers.
 */
interface PaymentGatewayInterface
{
    /**
     * Initialize payment collection
     */
    public function initiatePayment(PaymentRequest $request): PaymentResponse;

    /**
     * Verify payment status
     */
    public function verifyPayment(string $transactionReference): PaymentResponse;

    /**
     * Process refund
     */
    public function refundPayment(RefundRequest $request): RefundResponse;

    /**
     * Get gateway name
     */
    public function getName(): string;

    /**
     * Validate gateway configuration
     */
    public function validateConfiguration(array $credentials): array;

    /**
     * Get required configuration fields
     */
    public function getRequiredFields(): array;

    /**
     * Check if gateway supports test mode
     */
    public function supportsTestMode(): bool;
}
