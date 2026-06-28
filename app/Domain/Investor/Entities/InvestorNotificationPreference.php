<?php

namespace App\Domain\Investor\Entities;

class InvestorNotificationPreference
{
    public function __construct(
        private ?int $id,
        private int $investorAccountId,
        private bool $emailAnnouncements = true,
        private bool $emailFinancialReports = true,
        private bool $emailDividends = true,
        private bool $emailMeetings = true,
        private bool $emailMessages = true,
        private bool $emailUrgentOnly = false,
        private string $digestFrequency = 'immediate',
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null
    ) {}

    public static function createDefault(int $investorAccountId): self
    {
        return new self(
            id: null,
            investorAccountId: $investorAccountId,
            emailAnnouncements: true,
            emailFinancialReports: true,
            emailDividends: true,
            emailMeetings: true,
            emailMessages: true,
            emailUrgentOnly: false,
            digestFrequency: 'immediate'
        );
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getInvestorAccountId(): int { return $this->investorAccountId; }
    public function getEmailAnnouncements(): bool { return $this->emailAnnouncements; }
    public function getEmailFinancialReports(): bool { return $this->emailFinancialReports; }
    public function getEmailDividends(): bool { return $this->emailDividends; }
    public function getEmailMeetings(): bool { return $this->emailMeetings; }
    public function getEmailMessages(): bool { return $this->emailMessages; }
    public function getEmailUrgentOnly(): bool { return $this->emailUrgentOnly; }
    public function getDigestFrequency(): string { return $this->digestFrequency; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    // Business logic
    public function shouldReceiveEmail(string $type, bool $isUrgent = false): bool
    {
        // If urgent-only mode is enabled, only send urgent emails
        if ($this->emailUrgentOnly && !$isUrgent) {
            return false;
        }

        return match ($type) {
            'announcement' => $this->emailAnnouncements,
            'financial_report' => $this->emailFinancialReports,
            'dividend' => $this->emailDividends,
            'meeting' => $this->emailMeetings,
            'message' => $this->emailMessages,
            default => false,
        };
    }

    public function shouldSendImmediately(): bool
    {
        return $this->digestFrequency === 'immediate';
    }

    // Setters for updates
    public function updatePreferences(
        bool $emailAnnouncements,
        bool $emailFinancialReports,
        bool $emailDividends,
        bool $emailMeetings,
        bool $emailMessages,
        bool $emailUrgentOnly,
        string $digestFrequency
    ): void {
        $this->emailAnnouncements = $emailAnnouncements;
        $this->emailFinancialReports = $emailFinancialReports;
        $this->emailDividends = $emailDividends;
        $this->emailMeetings = $emailMeetings;
        $this->emailMessages = $emailMessages;
        $this->emailUrgentOnly = $emailUrgentOnly;
        $this->digestFrequency = $digestFrequency;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'investor_account_id' => $this->investorAccountId,
            'email_announcements' => $this->emailAnnouncements,
            'email_financial_reports' => $this->emailFinancialReports,
            'email_dividends' => $this->emailDividends,
            'email_meetings' => $this->emailMeetings,
            'email_messages' => $this->emailMessages,
            'email_urgent_only' => $this->emailUrgentOnly,
            'digest_frequency' => $this->digestFrequency,
        ];
    }
}
