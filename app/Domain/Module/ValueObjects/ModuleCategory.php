<?php

namespace App\Domain\Module\ValueObjects;

enum ModuleCategory: string
{
    case CORE = 'core';
    case PERSONAL = 'personal';
    case SME = 'sme';
    case ENTERPRISE = 'enterprise';

    public static function fromString(string $value): self
    {
        return self::tryFrom(strtolower($value)) ?? self::SME;
    }

    public function label(): string
    {
        return match($this) {
            self::CORE => 'Core Platform',
            self::PERSONAL => 'Personal Apps',
            self::SME => 'SME Business Tools',
            self::ENTERPRISE => 'Enterprise Solutions',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::CORE => 'Essential platform features',
            self::PERSONAL => 'Personal productivity and lifestyle apps',
            self::SME => 'Small and medium enterprise business tools',
            self::ENTERPRISE => 'Large-scale enterprise solutions',
        };
    }
}
