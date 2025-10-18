<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidPositionIdException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PositionId
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
            throw InvalidPositionIdException::empty();
        }

        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            throw InvalidPositionIdException::invalidFormat($id);
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

    public function equals(PositionId $other): bool
    {
        return $this->value->equals($other->value);
    }

    public function getValue(): UuidInterface
    {
        return $this->value;
    }
}