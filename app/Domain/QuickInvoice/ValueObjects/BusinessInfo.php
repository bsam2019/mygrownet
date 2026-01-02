<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\ValueObjects;

final class BusinessInfo
{
    private function __construct(
        private readonly string $name,
        private readonly ?string $address,
        private readonly ?string $phone,
        private readonly ?string $email,
        private readonly ?string $logo,
        private readonly ?string $taxNumber,
        private readonly ?string $website
    ) {}

    public static function create(
        string $name,
        ?string $address = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $logo = null,
        ?string $taxNumber = null,
        ?string $website = null
    ): self {
        return new self(
            name: trim($name),
            address: $address ? trim($address) : null,
            phone: $phone ? trim($phone) : null,
            email: $email ? trim($email) : null,
            logo: $logo,
            taxNumber: $taxNumber ? trim($taxNumber) : null,
            website: $website ? trim($website) : null
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function logo(): ?string
    {
        return $this->logo;
    }

    public function taxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function website(): ?string
    {
        return $this->website;
    }

    public function withLogo(string $logo): self
    {
        return new self(
            $this->name,
            $this->address,
            $this->phone,
            $this->email,
            $logo,
            $this->taxNumber,
            $this->website
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' => $this->logo,
            'tax_number' => $this->taxNumber,
            'website' => $this->website,
        ];
    }
}
