<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\PaymentTransactionId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class PaymentTransaction implements Arrayable
{
    private function __construct(
        private PaymentTransactionId $id,
        private CompanyId $companyId,
        private string $payableType,
        private int $payableId,
        private string $gateway,
        private ?string $transactionId,
        private float $amount,
        private string $currency,
        private string $status,
        private ?array $gatewayResponse,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, string $payableType, int $payableId, string $gateway, float $amount, string $currency = 'ZMW'): self
    {
        return new self(PaymentTransactionId::generate(), $companyId, $payableType, $payableId, $gateway, null, $amount, $currency, 'pending', null, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(PaymentTransactionId $id, CompanyId $companyId, string $payableType, int $payableId, string $gateway, ?string $transactionId, float $amount, string $currency, string $status, ?array $gatewayResponse, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $payableType, $payableId, $gateway, $transactionId, $amount, $currency, $status, $gatewayResponse, $createdAt, $updatedAt);
    }

    public function complete(string $transactionId, array $gatewayResponse = []): void
    {
        $this->transactionId = $transactionId;
        $this->gatewayResponse = $gatewayResponse;
        $this->status = 'completed';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function fail(array $gatewayResponse = []): void
    {
        $this->gatewayResponse = $gatewayResponse;
        $this->status = 'failed';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): PaymentTransactionId { return $this->id; }
    public function getGateway(): string { return $this->gateway; }
    public function getStatus(): string { return $this->status; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'payable_type' => $this->payableType,
            'payable_id' => $this->payableId,
            'gateway' => $this->gateway,
            'transaction_id' => $this->transactionId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'gateway_response' => $this->gatewayResponse,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
