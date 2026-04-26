<?php

namespace App\Domain\CMS\BizDocs\Adapters;

class CmsCustomerWrapper
{
    public function __construct(private mixed $customer) {}

    public function name(): string { return $this->customer?->name ?? 'Customer'; }
    public function email(): ?string { return $this->customer?->email; }
    public function phone(): ?string { return $this->customer?->phone; }
    public function address(): ?string { return $this->customer?->address; }
    public function tpin(): ?string { return $this->customer?->tax_number ?? null; }
    public function id(): int { return $this->customer?->id ?? 0; }
}
