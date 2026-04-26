<?php

namespace App\Domain\CMS\BizDocs\Adapters;

class CmsBusinessProfileWrapper
{
    public function __construct(private mixed $company) {}

    public function businessName(): string { return $this->company?->name ?? ''; }
    public function address(): ?string { return $this->company?->address; }
    public function phone(): ?string { return $this->company?->phone; }
    public function email(): ?string { return $this->company?->email; }
    public function tpin(): ?string { return $this->company?->tax_number; }
    public function logo(): ?string { return $this->company?->logo_path; }
    public function signatureImage(): ?string { return $this->company?->settings['signature_image'] ?? null; }
    public function stampImage(): ?string { return null; }
    public function defaultCurrency(): string { return $this->company?->settings['currency'] ?? 'ZMW'; }
    public function defaultTaxRate(): float { return 16.0; }
    public function defaultTerms(): ?string { return null; }
    public function defaultNotes(): ?string { return null; }
    public function defaultPaymentInstructions(): ?string { return $this->company?->invoice_footer; }
    public function bankName(): ?string { return $this->company?->settings['bank_name'] ?? null; }
    public function bankAccount(): ?string { return $this->company?->settings['bank_account'] ?? null; }
    public function bankBranch(): ?string { return $this->company?->settings['bank_branch'] ?? null; }
    public function preparedBy(): ?string { return null; }
    public function id(): int { return $this->company?->id ?? 0; }
    public function userId(): int { return 0; }
    public function website(): ?string { return $this->company?->website ?? null; }
}
