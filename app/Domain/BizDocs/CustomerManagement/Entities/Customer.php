<?php

namespace App\Domain\BizDocs\CustomerManagement\Entities;

class Customer
{
    private function __construct(
        private ?int $id,
        private int $businessId,
        private string $name,
        private ?string $address,
        private ?string $phone,
        private ?string $email,
        private ?string $tpin,
        private ?string $notes
    ) {
        $this->validate();
    }

    public static function create(
        int $businessId,
        string $name,
        ?string $address = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $tpin = null,
        ?string $notes = null
    ): self {
        return new self(null, $businessId, $name, $address, $phone, $email, $tpin, $notes);
    }

    public static function fromPersistence(
        int $id,
        int $businessId,
        string $name,
        ?string $address,
        ?string $phone,
        ?string $email,
        ?string $tpin,
        ?string $notes
    ): self {
        return new self($id, $businessId, $name, $address, $phone, $email, $tpin, $notes);
    }

    private function validate(): void
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('Customer name cannot be empty');
        }

        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }
    }

    public function updateContactInfo(
        ?string $address = null,
        ?string $phone = null,
        ?string $email = null
    ): void {
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        if ($address !== null) {
            $this->address = $address;
        }
        if ($phone !== null) {
            $this->phone = $phone;
        }
        if ($email !== null) {
            $this->email = $email;
        }
    }

    public function updateDetails(
        string $name,
        ?string $address = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $tpin = null,
        ?string $notes = null
    ): void {
        if (empty($name)) {
            throw new \InvalidArgumentException('Customer name cannot be empty');
        }

        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        $this->name = $name;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->tpin = $tpin;
        $this->notes = $notes;
    }

    public function updateNotes(string $notes): void
    {
        $this->notes = $notes;
    }

    // Getters
    public function id(): ?int
    {
        return $this->id;
    }

    public function businessId(): int
    {
        return $this->businessId;
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

    public function tpin(): ?string
    {
        return $this->tpin;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'businessId' => $this->businessId,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'tpin' => $this->tpin,
            'notes' => $this->notes,
        ];
    }
}
