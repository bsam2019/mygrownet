<?php

namespace App\Domain\CMS\BizDocs\Adapters;

class CmsDocumentItemWrapper
{
    public function __construct(private array $item) {}

    public function description(): string { return $this->item['description'] ?? ''; }
    public function quantity(): float { return (float)($this->item['quantity'] ?? 1); }
    public function unitPrice(): CmsMoneyWrapper { return new CmsMoneyWrapper($this->item['unit_price'] ?? 0); }
    public function taxRate(): float { return (float)($this->item['tax_rate'] ?? 0); }
    public function discountAmount(): CmsMoneyWrapper { return new CmsMoneyWrapper($this->item['discount_amount'] ?? 0); }
    public function lineTotal(): CmsMoneyWrapper { return new CmsMoneyWrapper($this->item['line_total'] ?? 0); }
    public function dimensions(): ?string  { return $this->item['dimensions'] ?? null; }
    public function dimensionsValue(): float { return (float)($this->item['dimensions_value'] ?? 1); }

    public function calculateLineTotal(): CmsMoneyWrapper
    {
        $total = $this->item['line_total'] ?? ($this->quantity() * ($this->item['unit_price'] ?? 0));
        return new CmsMoneyWrapper($total);
    }
}
