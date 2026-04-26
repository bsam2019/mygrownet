<?php

namespace App\Domain\CMS\BizDocs\Adapters;

class CmsDocumentWrapper
{
    public function __construct(private array $data) {}

    public function type(): object
    {
        return new class($this->data['type']) {
            public function __construct(private string $v) {}
            public function value(): string { return $this->v; }
        };
    }

    public function number(): object
    {
        return new class($this->data['number']) {
            public function __construct(private string $v) {}
            public function value(): string { return $this->v; }
        };
    }

    public function issueDate(): \DateTimeImmutable { return $this->data['issueDate']; }
    public function dueDate(): ?\DateTimeImmutable { return $this->data['dueDate']; }
    public function validityDate(): ?\DateTimeImmutable { return $this->data['dueDate']; }
    public function notes(): ?string { return $this->data['notes']; }
    public function terms(): ?string { return $this->data['terms']; }
    public function paymentInstructions(): ?string { return $this->data['paymentInstructions'] ?? null; }
    public function currency(): string { return $this->data['currency'] ?? 'ZMW'; }
    public function collectTax(): bool { return true; }
    public function templateId(): ?int { return null; }
    public function id(): ?int { return null; }
    public function businessId(): int { return 0; }
    public function customerId(): int { return 0; }

    public function items(): array
    {
        return array_map(
            fn($item) => new CmsDocumentItemWrapper($item),
            $this->data['items']
        );
    }

    public function calculateTotals(): array
    {
        return [
            'subtotal'       => new CmsMoneyWrapper($this->data['subtotal']),
            'tax_total'      => new CmsMoneyWrapper($this->data['taxTotal']),
            'discount_total' => new CmsMoneyWrapper($this->data['discountTotal']),
            'grand_total'    => new CmsMoneyWrapper($this->data['grandTotal']),
        ];
    }

    public function extra(string $key, mixed $default = null): mixed
    {
        return $this->data['extra'][$key] ?? $default;
    }
}
