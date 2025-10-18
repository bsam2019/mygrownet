<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidEmployeeIdException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EmployeeId
{
    private UuidInterface $value;

    private function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function fromString(string $id): self
    {
        if (empty(trim($id))) {
            throw InvalidEmployeeIdException::empty();
        }

        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            throw InvalidEmployeeIdException::invalidFormat($id);
        }

        return new self($uuid);
    }

    public static function fromUuid(UuidInterface $uuid): self
    {
        return new self($uuid);
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(EmployeeId $other): bool
    {
        return $this->value->equals($other->value);
    }

    public function getValue(): UuidInterface
    {
        return $this->value;
    }
}