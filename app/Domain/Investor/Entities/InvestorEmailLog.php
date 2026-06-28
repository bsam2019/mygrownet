<?php

namespace App\Domain\Investor\Entities;

class InvestorEmailLog
{
    public function __construct(
        private ?int $id,
        private int $investorAccountId,
        private string $emailType,
        private string $subject,
        private ?int $referenceId = null,
        private ?string $referenceType = null,
        private string $status = 'pending',
        private ?\DateTimeImmutable $sentAt = null,
        private ?\DateTimeImmutable $openedAt = null,
        private ?\DateTimeImmutable $clickedAt = null,
        private ?string $errorMessage = null,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null
    ) {}

    public static function create(
        int $investorAccountId,
        string $emailType,
        string $subject,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): self {
        return new self(
            id: null,
            investorAccountId: $investorAccountId,
            emailType: $emailType,
            subject: $subject,
            referenceId: $referenceId,
            referenceType: $referenceType,
            status: 'pending',
            createdAt: new \DateTimeImmutable()
        );
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getInvestorAccountId(): int { return $this->investorAccountId; }
    public function getEmailType(): string { return $this->emailType; }
    public function getSubject(): string { return $this->subject; }
    public function getReferenceId(): ?int { return $this->referenceId; }
    public function getReferenceType(): ?string { return $this->referenceType; }
    public function getStatus(): string { return $this->status; }
    public function getSentAt(): ?\DateTimeImmutable { return $this->sentAt; }
    public function getOpenedAt(): ?\DateTimeImmutable { return $this->openedAt; }
    public function getClickedAt(): ?\DateTimeImmutable { return $this->clickedAt; }
    public function getErrorMessage(): ?string { return $this->errorMessage; }

    // Status updates
    public function markAsSent(): void
    {
        $this->status = 'sent';
        $this->sentAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->status = 'failed';
        $this->errorMessage = $errorMessage;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsOpened(): void
    {
        if (!$this->openedAt) {
            $this->openedAt = new \DateTimeImmutable();
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function markAsClicked(): void
    {
        if (!$this->clickedAt) {
            $this->clickedAt = new \DateTimeImmutable();
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isSent(): bool { return $this->status === 'sent'; }
    public function isFailed(): bool { return $this->status === 'failed'; }
    public function wasOpened(): bool { return $this->openedAt !== null; }
    public function wasClicked(): bool { return $this->clickedAt !== null; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'investor_account_id' => $this->investorAccountId,
            'email_type' => $this->emailType,
            'subject' => $this->subject,
            'reference_id' => $this->referenceId,
            'reference_type' => $this->referenceType,
            'status' => $this->status,
            'sent_at' => $this->sentAt?->format('Y-m-d H:i:s'),
            'opened_at' => $this->openedAt?->format('Y-m-d H:i:s'),
            'clicked_at' => $this->clickedAt?->format('Y-m-d H:i:s'),
            'error_message' => $this->errorMessage,
        ];
    }
}
