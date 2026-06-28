<?php

namespace App\Domain\Payment\ValueObjects;

enum PaymentType: string
{
    case SUBSCRIPTION = 'subscription';
    case WORKSHOP = 'workshop';
    case PRODUCT = 'product';
    case LEARNING_PACK = 'learning_pack';
    case COACHING = 'coaching';
    case UPGRADE = 'upgrade';
    case WALLET_TOPUP = 'wallet_topup';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::SUBSCRIPTION => 'Monthly Subscription',
            self::WORKSHOP => 'Workshop/Training',
            self::PRODUCT => 'Product Purchase',
            self::LEARNING_PACK => 'Learning Pack',
            self::COACHING => 'Coaching/Mentorship',
            self::UPGRADE => 'Tier Upgrade',
            self::WALLET_TOPUP => 'Wallet Top-Up',
            self::OTHER => 'Other',
        };
    }

    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
