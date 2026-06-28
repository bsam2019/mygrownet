<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\ValueObjects;

final class ClientInfo
{
    private function __construct(
        private readonly string $name,
        private readonly ?string $address,
        private readonly ?string $phone,
        private readonly ?string $email
    ) {}

    public static function create(
        string $name,
        ?string $address = null,
        ?string $phone = null,
        ?string $email = null
    ): self {
        return new self(
            name: trim($name),
            address: $address ? trim($address) : null,
            phone: $phone ? trim($phone) : null,
            email: $email ? trim($email) : null
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

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }
}
