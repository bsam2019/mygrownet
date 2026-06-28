<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorMessage
{
    private function __construct(
        private ?int $id,
        private int $investorAccountId,
        private ?int $adminId,
        private string $subject,
        private string $content,
        private string $direction, // 'inbound' (investor to admin) or 'outbound' (admin to investor)
        private bool $isRead,
        private ?DateTimeImmutable $readAt,
        private ?int $parentId,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $investorAccountId,
        ?int $adminId,
        string $subject,
        string $content,
        string $direction,
        ?int $parentId = null
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: null,
            investorAccountId: $investorAccountId,
            adminId: $adminId,
            subject: $subject,
            content: $content,
            direction: $direction,
            isRead: false,
            readAt: null,
            parentId: $parentId,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function reconstitute(
        int $id,
        int $investorAccountId,
        ?int $adminId,
        string $subject,
        string $content,
        string $direction,
        bool $isRead,
        ?DateTimeImmutable $readAt,
        ?int $parentId,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            investorAccountId: $investorAccountId,
            adminId: $adminId,
            subject: $subject,
            content: $content,
            direction: $direction,
            isRead: $isRead,
            readAt: $readAt,
            parentId: $parentId,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getInvestorAccountId(): int { return $this->investorAccountId; }
    public function getAdminId(): ?int { return $this->adminId; }
    public function getSubject(): string { return $this->subject; }
    public function getContent(): string { return $this->content; }
    public function getDirection(): string { return $this->direction; }
    public function isRead(): bool { return $this->isRead; }
    public function getReadAt(): ?DateTimeImmutable { return $this->readAt; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function isInbound(): bool { return $this->direction === 'inbound'; }
    public function isOutbound(): bool { return $this->direction === 'outbound'; }

    public function getPreview(int $length = 100): string
    {
        $stripped = strip_tags($this->content);
        return strlen($stripped) > $length 
            ? substr($stripped, 0, $length) . '...' 
            : $stripped;
    }

    public function markAsRead(): void
    {
        if (!$this->isRead) {
            $this->isRead = true;
            $this->readAt = new DateTimeImmutable();
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'investor_account_id' => $this->investorAccountId,
            'admin_id' => $this->adminId,
            'subject' => $this->subject,
            'content' => $this->content,
            'preview' => $this->getPreview(),
            'direction' => $this->direction,
            'is_read' => $this->isRead,
            'read_at' => $this->readAt?->format('Y-m-d H:i:s'),
            'parent_id' => $this->parentId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'created_at_human' => $this->getHumanReadableDate(),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    private function getHumanReadableDate(): string
    {
        $now = new DateTimeImmutable();
        $diff = $now->diff($this->createdAt);
        
        if ($diff->days === 0) {
            if ($diff->h === 0) {
                return $diff->i <= 1 ? 'Just now' : "{$diff->i} minutes ago";
            }
            return $diff->h === 1 ? '1 hour ago' : "{$diff->h} hours ago";
        }
        if ($diff->days === 1) return 'Yesterday';
        if ($diff->days < 7) return "{$diff->days} days ago";
        
        return $this->createdAt->format('M j, Y');
    }
}
