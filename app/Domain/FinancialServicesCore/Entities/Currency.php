<?php

namespace App\Domain\FinancialServicesCore\Entities;

class Currency
{
    private function __construct(
        private readonly ?int $id,
        private string $code,
        private string $name,
        private string $symbol,
        private int $decimalPlaces,
        private bool $isActive,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        string $code,
        string $name,
        string $symbol,
        int $decimalPlaces = 2,
    ): self {
        return new self(
            id: null,
            code: strtoupper($code),
            name: $name,
            symbol: $symbol,
            decimalPlaces: $decimalPlaces,
            isActive: true,
            createdAt: new \DateTimeImmutable(),
            updatedAt: null,
        );
    }

    public static function reconstitute(
        int $id,
        string $code,
        string $name,
        string $symbol,
        int $decimalPlaces,
        bool $isActive,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            code: $code,
            name: $name,
            symbol: $symbol,
            decimalPlaces: $decimalPlaces,
            isActive: $isActive,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function disable(): void
    {
        $this->isActive = false;
    }

    public function enable(): void
    {
        $this->isActive = true;
    }

    public function id(): ?int { return $this->id; }
    public function code(): string { return $this->code; }
    public function name(): string { return $this->name; }
    public function symbol(): string { return $this->symbol; }
    public function decimalPlaces(): int { return $this->decimalPlaces; }
    public function isActive(): bool { return $this->isActive; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'decimal_places' => $this->decimalPlaces,
            'is_active' => $this->isActive,
        ];
    }
}
