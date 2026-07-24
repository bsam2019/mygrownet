<?php

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Company
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?int $organizationId,
        public readonly ?string $industryType,
        public readonly ?string $businessRegistrationNumber,
        public readonly ?string $taxNumber,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $country,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $website,
        public readonly ?string $logoPath,
        public readonly ?string $invoiceFooter,
        public readonly string $status,
        public readonly ?string $subscriptionType,
        public readonly ?string $sponsorReference,
        public readonly ?string $subscriptionNotes,
        public readonly ?DateTimeImmutable $complimentaryUntil,
        public readonly array $settings,
        public readonly bool $onboardingCompleted,
        public readonly array $onboardingProgress,
        public readonly ?DateTimeImmutable $onboardingCompletedAt,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'],
            organizationId: $data['organization_id'] ?? null,
            industryType: $data['industry_type'] ?? null,
            businessRegistrationNumber: $data['business_registration_number'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            website: $data['website'] ?? null,
            logoPath: $data['logo_path'] ?? null,
            invoiceFooter: $data['invoice_footer'] ?? null,
            status: $data['status'] ?? 'active',
            subscriptionType: $data['subscription_type'] ?? null,
            sponsorReference: $data['sponsor_reference'] ?? null,
            subscriptionNotes: $data['subscription_notes'] ?? null,
            complimentaryUntil: isset($data['complimentary_until']) ? new DateTimeImmutable($data['complimentary_until']) : null,
            settings: $data['settings'] ?? [],
            onboardingCompleted: (bool) ($data['onboarding_completed'] ?? false),
            onboardingProgress: $data['onboarding_progress'] ?? [],
            onboardingCompletedAt: isset($data['onboarding_completed_at']) ? new DateTimeImmutable($data['onboarding_completed_at']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public static function create(array $data): self
    {
        return new self(
            id: null,
            name: $data['name'],
            organizationId: $data['organization_id'] ?? null,
            industryType: $data['industry_type'] ?? null,
            businessRegistrationNumber: $data['business_registration_number'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? 'Lusaka',
            country: $data['country'] ?? 'Zambia',
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            website: $data['website'] ?? null,
            logoPath: $data['logo_path'] ?? null,
            invoiceFooter: $data['invoice_footer'] ?? null,
            status: 'active',
            subscriptionType: $data['subscription_type'] ?? 'complimentary',
            sponsorReference: $data['sponsor_reference'] ?? null,
            subscriptionNotes: $data['subscription_notes'] ?? null,
            complimentaryUntil: isset($data['complimentary_until']) ? $data['complimentary_until'] : new DateTimeImmutable('+14 days'),
            settings: $data['settings'] ?? [],
            onboardingCompleted: false,
            onboardingProgress: [],
            onboardingCompletedAt: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasValidAccess(): bool
    {
        if (!$this->isActive()) return false;
        return true;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'organization_id' => $this->organizationId,
            'industry_type' => $this->industryType,
            'business_registration_number' => $this->businessRegistrationNumber,
            'tax_number' => $this->taxNumber,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'logo_path' => $this->logoPath,
            'invoice_footer' => $this->invoiceFooter,
            'status' => $this->status,
            'subscription_type' => $this->subscriptionType,
            'sponsor_reference' => $this->sponsorReference,
            'subscription_notes' => $this->subscriptionNotes,
            'complimentary_until' => $this->complimentaryUntil?->format('Y-m-d H:i:s'),
            'settings' => $this->settings,
            'onboarding_completed' => $this->onboardingCompleted,
            'onboarding_progress' => $this->onboardingProgress,
            'onboarding_completed_at' => $this->onboardingCompletedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
