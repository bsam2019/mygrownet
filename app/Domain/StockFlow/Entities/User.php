<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;

class User
{
    private function __construct(
        private UserId $id,
        private string $name,
        private string $email,
        private string $password,
        private ?string $rememberToken,
        private ?DateTimeImmutable $emailVerifiedAt,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        string $name,
        string $email,
        string $password,
    ): self {
        return new self(
            id: UserId::generate(),
            name: $name,
            email: $email,
            password: $password,
            rememberToken: null,
            emailVerifiedAt: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        UserId $id,
        string $name,
        string $email,
        string $password,
        ?string $rememberToken,
        ?DateTimeImmutable $emailVerifiedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $name, $email, $password, $rememberToken, $emailVerifiedAt, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): UserId { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRememberToken(): ?string { return $this->rememberToken; }
    public function getEmailVerifiedAt(): ?DateTimeImmutable { return $this->emailVerifiedAt; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
