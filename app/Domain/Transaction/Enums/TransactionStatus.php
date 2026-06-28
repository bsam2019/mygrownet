<?php

namespace App\Domain\Transaction\Enums;

/**
 * Transaction Status Enum
 * 
 * Defines all possible transaction statuses in the system.
 * Provides type safety and state management.
 */
enum TransactionStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REVERSED = 'reversed';

    /**
     * Check if transaction is in a final state
     */
    public function isFinal(): bool
    {
        return match($this) {
            self::COMPLETED, self::FAILED, self::CANCELLED, self::REVERSED => true,
            default => false,
        };
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if transaction can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return match($this) {
            self::PENDING, self::PROCESSING => true,
            default => false,
        };
    }

    /**
     * Check if transaction can be reversed
     */
    public function canBeReversed(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if transaction affects balance
     */
    public function affectsBalance(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REVERSED => 'Reversed',
        };
    }

    /**
     * Get color class for UI display
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::PROCESSING => 'blue',
            self::COMPLETED => 'green',
            self::FAILED => 'red',
            self::CANCELLED => 'gray',
            self::REVERSED => 'orange',
        };
    }

    /**
     * Get icon class for UI display
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'clock',
            self::PROCESSING => 'arrow-path',
            self::COMPLETED => 'check-circle',
            self::FAILED => 'x-circle',
            self::CANCELLED => 'ban',
            self::REVERSED => 'arrow-uturn-left',
        };
    }

    /**
     * Get badge class for UI display
     */
    public function badgeClass(): string
    {
        $color = $this->color();
        return "bg-{$color}-100 text-{$color}-800 border-{$color}-200";
    }
}
