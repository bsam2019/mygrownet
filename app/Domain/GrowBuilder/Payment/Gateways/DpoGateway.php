<?php

namespace App\Domain\GrowBuilder\Payment\Gateways;

use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;
use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;

class DpoGateway extends AbstractPaymentGateway
{
    private string $baseUrl;

    public function __construct(array $credentials, bool $testMode = false)
    {
        parent::__construct($credentials, $testMode);
        $this->baseUrl = 'https://secure.3gdirectpay.com';
    }

    public function initiatePayment(PaymentRequest $request): PaymentResponse
    {
        try {
            $this->logActivity('Initiating DPO payment', $request->toArray());

            // Create payment token
            $xml = $this->buildCreateTokenXml($request);
            
            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/API/v6/",
                $xml,
                [
                    'Content-Type' => 'application/xml',
                ]
            );

            if ($response['success']) {
                $responseXml = simplexml_load_string($response['body']);
                
                if ($responseXml && (string)$responseXml->Result === '000') {
                    $transToken = (string)$responseXml->TransToken;
                    $checkoutUrl = "{$this->baseUrl}/payv2.php?ID={$transToken}";

                    return new PaymentResponse(
                        success: true,
                        status: PaymentStatus::PENDING,
                        transactionReference: $request->reference,
                        externalReference: $transToken,
                        message: 'Payment initiated successfully',
                        rawResponse: json_decode(json_encode($responseXml), true),
                        checkoutUrl: $checkoutUrl,
                    );
                }

                return new PaymentResponse(
                    success: false,
                    status: PaymentStatus::FAILED,
                    transactionReference: $request->reference,
                    message: (string)$responseXml->ResultExplanation ?? 'Payment initiation failed',
                    rawResponse: json_decode(json_encode($responseXml), true),
                );
            }

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $request->reference,
                message: 'Payment initiation failed',
            );

        } catch (\Exception $e) {
            $this->logError('DPO payment initiation', $e, $request->toArray());

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $request->reference,
                message: $e->getMessage(),
            );
        }
    }

    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        try {
            $xml = $this->buildVerifyTokenXml($transactionReference);

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/API/v6/",
                $xml,
                [
                    'Content-Type' => 'application/xml',
                ]
            );

            if ($response['success']) {
                $responseXml = simplexml_load_string($response['body']);
                
                if ($responseXml && (string)$responseXml->Result === '000') {
                    $status = $this->mapStatus((string)$responseXml->TransactionStatus);

                    return new PaymentResponse(
                        success: $status === PaymentStatus::COMPLETED,
                        status: $status,
                        transactionReference: $transactionReference,
                        externalReference: (string)$responseXml->TransToken,
                        rawResponse: json_decode(json_encode($responseXml), true),
                    );
                }
            }

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $transactionReference,
                message: 'Failed to verify payment',
            );

        } catch (\Exception $e) {
            $this->logError('DPO payment verification', $e, ['reference' => $transactionReference]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $transactionReference,
                message: $e->getMessage(),
            );
        }
    }

    public function refundPayment(RefundRequest $request): RefundResponse
    {
        try {
            $xml = $this->buildRefundXml($request);

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/API/v6/",
                $xml,
                [
                    'Content-Type' => 'application/xml',
                ]
            );

            if ($response['success']) {
                $responseXml = simplexml_load_string($response['body']);
                
                if ($responseXml && (string)$responseXml->Result === '000') {
                    return new RefundResponse(
                        success: true,
                        refundReference: (string)$responseXml->TransToken ?? '',
                        message: 'Refund processed successfully',
                        rawResponse: json_decode(json_encode($responseXml), true),
                    );
                }

                return new RefundResponse(
                    success: false,
                    refundReference: '',
                    message: (string)$responseXml->ResultExplanation ?? 'Refund failed',
                    rawResponse: json_decode(json_encode($responseXml), true),
                );
            }

            return new RefundResponse(
                success: false,
                refundReference: '',
                message: 'Refund request failed',
            );

        } catch (\Exception $e) {
            $this->logError('DPO refund processing', $e, $request->toArray());

            return new RefundResponse(
                success: false,
                refundReference: '',
                message: $e->getMessage(),
            );
        }
    }

    public function getName(): string
    {
        return 'DPO PayGate';
    }

    public function validateConfiguration(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['company_token'])) {
            $errors[] = 'Company token is required';
        }

        if (empty($credentials['service_type'])) {
            $errors[] = 'Service type is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function getRequiredFields(): array
    {
        return [
            [
                'name' => 'company_token',
                'label' => 'Company Token',
                'type' => 'password',
                'required' => true,
                'description' => 'Your DPO company token',
            ],
            [
                'name' => 'service_type',
                'label' => 'Service Type',
                'type' => 'text',
                'required' => true,
                'description' => 'Your DPO service type (e.g., 3854)',
            ],
        ];
    }

    public function supportsTestMode(): bool
    {
        return false;
    }

    private function buildCreateTokenXml(PaymentRequest $request): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <CompanyToken>{$this->credentials['company_token']}</CompanyToken>
    <Request>createToken</Request>
    <Transaction>
        <PaymentAmount>{$request->amount}</PaymentAmount>
        <PaymentCurrency>{$request->currency}</PaymentCurrency>
        <CompanyRef>{$request->reference}</CompanyRef>
        <RedirectURL>{$request->returnUrl}</RedirectURL>
        <BackURL>{$request->returnUrl}</BackURL>
        <CompanyRefUnique>1</CompanyRefUnique>
        <PTL>5</PTL>
    </Transaction>
    <Services>
        <Service>
            <ServiceType>{$this->credentials['service_type']}</ServiceType>
            <ServiceDescription>{$request->description}</ServiceDescription>
            <ServiceDate></ServiceDate>
        </Service>
    </Services>
</API3G>
XML;
    }

    private function buildVerifyTokenXml(string $transToken): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <CompanyToken>{$this->credentials['company_token']}</CompanyToken>
    <Request>verifyToken</Request>
    <TransactionToken>{$transToken}</TransactionToken>
</API3G>
XML;
    }

    private function buildRefundXml(RefundRequest $request): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <CompanyToken>{$this->credentials['company_token']}</CompanyToken>
    <Request>refundToken</Request>
    <TransactionToken>{$request->transactionReference}</TransactionToken>
    <RefundAmount>{$request->amount}</RefundAmount>
    <RefundDetails>{$request->reason}</RefundDetails>
</API3G>
XML;
    }

    private function mapStatus(string $dpoStatus): PaymentStatus
    {
        return match((int)$dpoStatus) {
            1 => PaymentStatus::COMPLETED,
            2 => PaymentStatus::FAILED,
            3 => PaymentStatus::CANCELLED,
            4 => PaymentStatus::PENDING,
            default => PaymentStatus::PROCESSING,
        };
    }
}
