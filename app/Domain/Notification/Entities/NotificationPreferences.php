<?php

namespace App\Domain\Notification\Entities;

class NotificationPreferences
{
    private function __construct(
        private readonly int $userId,
        private bool $emailEnabled,
        private bool $smsEnabled,
        private bool $pushEnabled,
        private bool $inAppEnabled,
        private array $categoryPreferences,
        private string $digestFrequency
    ) {}

    public static function createDefault(int $userId): self
    {
        return new self(
            userId: $userId,
            emailEnabled: true,
            smsEnabled: false,
            pushEnabled: false,
            inAppEnabled: true,
            categoryPreferences: [
                'wallet' => true,
                'commissions' => true,
                'withdrawals' => true,
                'subscriptions' => true,
                'referrals' => true,
                'workshops' => true,
                'ventures' => true,
                'bgf' => true,
                'points' => true,
                'security' => true,
                'marketing' => false,
            ],
            digestFrequency: 'instant'
        );
    }

    public function enableChannel(string $channel): void
    {
        match($channel) {
            'email' => $this->emailEnabled = true,
            'sms' => $this->smsEnabled = true,
            'push' => $this->pushEnabled = true,
            'in_app' => $this->inAppEnabled = true,
            default => throw new \InvalidArgumentException("Invalid channel: {$channel}")
        };
    }

    public function disableChannel(string $channel): void
    {
        match($channel) {
            'email' => $this->emailEnabled = false,
            'sms' => $this->smsEnabled = false,
            'push' => $this->pushEnabled = false,
            'in_app' => $this->inAppEnabled = false,
            default => throw new \InvalidArgumentException("Invalid channel: {$channel}")
        };
    }

    public function enableCategory(string $category): void
    {
        $this->categoryPreferences[$category] = true;
    }

    public function disableCategory(string $category): void
    {
        $this->categoryPreferences[$category] = false;
    }

    public function isCategoryEnabled(string $category): bool
    {
        return $this->categoryPreferences[$category] ?? false;
    }

    public function getEnabledChannels(): array
    {
        $channels = [];
        
        if ($this->emailEnabled) $channels[] = 'email';
        if ($this->smsEnabled) $channels[] = 'sms';
        if ($this->pushEnabled) $channels[] = 'push';
        if ($this->inAppEnabled) $channels[] = 'in_app';
        
        return $channels;
    }

    // Getters
    public function userId(): int
    {
        return $this->userId;
    }

    public function isEmailEnabled(): bool
    {
        return $this->emailEnabled;
    }

    public function isSmsEnabled(): bool
    {
        return $this->smsEnabled;
    }

    public function isPushEnabled(): bool
    {
        return $this->pushEnabled;
    }

    public function isInAppEnabled(): bool
    {
        return $this->inAppEnabled;
    }

    public function digestFrequency(): string
    {
        return $this->digestFrequency;
    }
}
