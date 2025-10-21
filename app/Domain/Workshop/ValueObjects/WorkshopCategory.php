<?php

namespace App\Domain\Workshop\ValueObjects;

class WorkshopCategory
{
    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::validCategories())) {
            throw new \InvalidArgumentException("Invalid workshop category: {$value}");
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function validCategories(): array
    {
        return [
            'financial_literacy',
            'business_skills',
            'leadership',
            'marketing',
            'technology',
            'personal_development',
        ];
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match($this->value) {
            'financial_literacy' => 'Financial Literacy',
            'business_skills' => 'Business Skills',
            'leadership' => 'Leadership',
            'marketing' => 'Marketing',
            'technology' => 'Technology',
            'personal_development' => 'Personal Development',
        };
    }
}
