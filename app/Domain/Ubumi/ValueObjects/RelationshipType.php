<?php

namespace App\Domain\Ubumi\ValueObjects;

use InvalidArgumentException;

/**
 * Relationship Type Value Object
 * 
 * Represents the type of relationship between two persons
 */
class RelationshipType
{
    // Parent-Child relationships
    public const PARENT = 'parent';
    public const CHILD = 'child';
    
    // Sibling relationships
    public const SIBLING = 'sibling';
    
    // Spousal relationships
    public const SPOUSE = 'spouse';
    public const PARTNER = 'partner';
    
    // Extended family
    public const GRANDPARENT = 'grandparent';
    public const GRANDCHILD = 'grandchild';
    public const AUNT_UNCLE = 'aunt_uncle';
    public const NIECE_NEPHEW = 'niece_nephew';
    public const COUSIN = 'cousin';
    
    // Guardian relationships
    public const GUARDIAN = 'guardian';
    public const WARD = 'ward';

    private string $value;

    private function __construct(string $value)
    {
        if (!self::isValid($value)) {
            throw new InvalidArgumentException("Invalid relationship type: {$value}");
        }
        
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function parent(): self
    {
        return new self(self::PARENT);
    }

    public static function child(): self
    {
        return new self(self::CHILD);
    }

    public static function sibling(): self
    {
        return new self(self::SIBLING);
    }

    public static function spouse(): self
    {
        return new self(self::SPOUSE);
    }

    public static function partner(): self
    {
        return new self(self::PARTNER);
    }

    public static function grandparent(): self
    {
        return new self(self::GRANDPARENT);
    }

    public static function grandchild(): self
    {
        return new self(self::GRANDCHILD);
    }

    public static function auntUncle(): self
    {
        return new self(self::AUNT_UNCLE);
    }

    public static function nieceNephew(): self
    {
        return new self(self::NIECE_NEPHEW);
    }

    public static function cousin(): self
    {
        return new self(self::COUSIN);
    }

    public static function guardian(): self
    {
        return new self(self::GUARDIAN);
    }

    public static function ward(): self
    {
        return new self(self::WARD);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(RelationshipType $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Get the inverse relationship type
     * e.g., parent -> child, spouse -> spouse
     */
    public function getInverse(): self
    {
        return match($this->value) {
            self::PARENT => self::child(),
            self::CHILD => self::parent(),
            self::SIBLING => self::sibling(),
            self::SPOUSE => self::spouse(),
            self::PARTNER => self::partner(),
            self::GRANDPARENT => self::grandchild(),
            self::GRANDCHILD => self::grandparent(),
            self::AUNT_UNCLE => self::nieceNephew(),
            self::NIECE_NEPHEW => self::auntUncle(),
            self::COUSIN => self::cousin(),
            self::GUARDIAN => self::ward(),
            self::WARD => self::guardian(),
        };
    }

    /**
     * Check if this is a reciprocal relationship (same both ways)
     */
    public function isReciprocal(): bool
    {
        return in_array($this->value, [
            self::SIBLING,
            self::SPOUSE,
            self::PARTNER,
            self::COUSIN,
        ]);
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, [
            self::PARENT,
            self::CHILD,
            self::SIBLING,
            self::SPOUSE,
            self::PARTNER,
            self::GRANDPARENT,
            self::GRANDCHILD,
            self::AUNT_UNCLE,
            self::NIECE_NEPHEW,
            self::COUSIN,
            self::GUARDIAN,
            self::WARD,
        ]);
    }

    public static function all(): array
    {
        return [
            self::PARENT,
            self::CHILD,
            self::SIBLING,
            self::SPOUSE,
            self::PARTNER,
            self::GRANDPARENT,
            self::GRANDCHILD,
            self::AUNT_UNCLE,
            self::NIECE_NEPHEW,
            self::COUSIN,
            self::GUARDIAN,
            self::WARD,
        ];
    }

    public function getLabel(): string
    {
        return match($this->value) {
            self::PARENT => 'Parent',
            self::CHILD => 'Child',
            self::SIBLING => 'Sibling',
            self::SPOUSE => 'Spouse',
            self::PARTNER => 'Partner',
            self::GRANDPARENT => 'Grandparent',
            self::GRANDCHILD => 'Grandchild',
            self::AUNT_UNCLE => 'Aunt/Uncle',
            self::NIECE_NEPHEW => 'Niece/Nephew',
            self::COUSIN => 'Cousin',
            self::GUARDIAN => 'Guardian',
            self::WARD => 'Ward',
        };
    }

    /**
     * Check if this is a parent-child relationship
     */
    public function isParentChildRelationship(): bool
    {
        return in_array($this->value, [self::PARENT, self::CHILD]);
    }

    /**
     * Check if this is a parent type (person is the parent)
     */
    public function isParentType(): bool
    {
        return $this->value === self::PARENT;
    }

    /**
     * Check if this is a grandparent-grandchild relationship
     */
    public function isGrandparentRelationship(): bool
    {
        return in_array($this->value, [self::GRANDPARENT, self::GRANDCHILD]);
    }

    /**
     * Check if this is a grandparent type (person is the grandparent)
     */
    public function isGrandparentType(): bool
    {
        return $this->value === self::GRANDPARENT;
    }

    /**
     * Check if this is a spouse/partner relationship
     */
    public function isSpouseType(): bool
    {
        return in_array($this->value, [self::SPOUSE, self::PARTNER]);
    }
}
