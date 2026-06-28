<?php

namespace App\Domain\Transaction\ValueObjects;

use App\Models\Module;

/**
 * Transaction Source Value Object
 * 
 * Represents the source module/system that generated a transaction.
 * Enables multi-module financial tracking and reporting.
 * 
 * Uses database-driven module registry for scalability.
 */
final class TransactionSource
{
    private function __construct(
        private readonly string $source
    ) {
        if (!$this->isValid($source)) {
            throw new \InvalidArgumentException("Invalid transaction source: {$source}");
        }
    }

    /**
     * Create from string
     */
    public static function from(string $source): self
    {
        return new self($source);
    }

    /**
     * Get the source value
     */
    public function value(): string
    {
        return $this->source;
    }

    /**
     * Check if source is valid (exists in modules table)
     */
    private function isValid(string $source): bool
    {
        return Module::exists($source);
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        $module = Module::findByCode($this->source);
        return $module ? $module->name : $this->source;
    }

    /**
     * Check if this is a revenue-generating module
     */
    public function isRevenueModule(): bool
    {
        $revenueModules = Module::revenueModules();
        return isset($revenueModules[$this->source]);
    }

    /**
     * Check if this is an earnings source
     */
    public function isEarningsSource(): bool
    {
        return in_array($this->source, ['lgr', 'commissions', 'profit_share']);
    }

    /**
     * Get all valid sources
     */
    public static function all(): array
    {
        return array_keys(Module::active());
    }

    /**
     * Get revenue-generating modules
     */
    public static function revenueModules(): array
    {
        return array_keys(Module::revenueModules());
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->source;
    }

    /**
     * Check equality
     */
    public function equals(TransactionSource $other): bool
    {
        return $this->source === $other->source;
    }
}
