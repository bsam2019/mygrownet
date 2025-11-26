<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorAnnouncement;
use App\Domain\Investor\Repositories\InvestorAnnouncementRepositoryInterface;
use App\Domain\Investor\ValueObjects\AnnouncementType;
use App\Domain\Investor\ValueObjects\AnnouncementPriority;
use DateTimeImmutable;

class AnnouncementService
{
    public function __construct(
        private InvestorAnnouncementRepositoryInterface $repository
    ) {}

    public function createAnnouncement(array $data): InvestorAnnouncement
    {
        $announcement = InvestorAnnouncement::create(
            title: $data['title'],
            content: $data['content'],
            summary: $data['summary'] ?? null,
            type: AnnouncementType::from($data['type'] ?? 'general'),
            priority: AnnouncementPriority::from($data['priority'] ?? 'normal'),
            isPinned: $data['is_pinned'] ?? false,
            sendEmail: $data['send_email'] ?? false,
            expiresAt: isset($data['expires_at']) ? new DateTimeImmutable($data['expires_at']) : null,
            createdBy: $data['created_by'] ?? null
        );

        $saved = $this->repository->save($announcement);

        // Auto-publish if requested
        if ($data['publish_immediately'] ?? false) {
            $saved = $this->publishAnnouncement($saved->getId());
        }

        return $saved;
    }

    public function updateAnnouncement(int $id, array $data): ?InvestorAnnouncement
    {
        $existing = $this->repository->findById($id);
        if (!$existing) {
            return null;
        }

        $updated = InvestorAnnouncement::fromArray(array_merge(
            $existing->toArray(),
            [
                'title' => $data['title'] ?? $existing->getTitle(),
                'content' => $data['content'] ?? $existing->getContent(),
                'summary' => $data['summary'] ?? $existing->getSummary(),
                'type' => $data['type'] ?? $existing->getType()->value,
                'priority' => $data['priority'] ?? $existing->getPriority()->value,
                'is_pinned' => $data['is_pinned'] ?? $existing->isPinned(),
                'send_email' => $data['send_email'] ?? $existing->shouldSendEmail(),
                'expires_at' => $data['expires_at'] ?? $existing->getExpiresAt()?->format('Y-m-d H:i:s'),
            ]
        ));

        return $this->repository->save($updated);
    }

    public function publishAnnouncement(int $id): ?InvestorAnnouncement
    {
        $announcement = $this->repository->findById($id);
        if (!$announcement) {
            return null;
        }

        $published = $announcement->publish();
        return $this->repository->save($published);
    }

    public function unpublishAnnouncement(int $id): ?InvestorAnnouncement
    {
        $announcement = $this->repository->findById($id);
        if (!$announcement) {
            return null;
        }

        $unpublished = $announcement->unpublish();
        return $this->repository->save($unpublished);
    }

    public function deleteAnnouncement(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getAnnouncementById(int $id): ?InvestorAnnouncement
    {
        return $this->repository->findById($id);
    }

    public function getAllAnnouncements(): array
    {
        return $this->repository->findAll();
    }

    public function getActiveAnnouncements(): array
    {
        return $this->repository->findActive();
    }

    public function getAnnouncementsForInvestor(int $investorAccountId): array
    {
        $announcements = $this->repository->findActive();
        
        return array_map(function ($announcement) use ($investorAccountId) {
            $isRead = $this->repository->isReadByInvestor($announcement->getId(), $investorAccountId);
            return [
                'announcement' => $announcement->toArray(),
                'is_read' => $isRead,
            ];
        }, $announcements);
    }

    public function getUnreadAnnouncementsForInvestor(int $investorAccountId): array
    {
        return $this->repository->getUnreadForInvestor($investorAccountId);
    }

    public function markAsRead(int $announcementId, int $investorAccountId): void
    {
        $this->repository->markAsRead($announcementId, $investorAccountId);
    }

    public function getAnnouncementStats(int $id): array
    {
        $announcement = $this->repository->findById($id);
        if (!$announcement) {
            return [];
        }

        return [
            'id' => $id,
            'title' => $announcement->getTitle(),
            'read_count' => $this->repository->getReadCount($id),
            'is_published' => $announcement->isPublished(),
            'is_expired' => $announcement->isExpired(),
            'published_at' => $announcement->getPublishedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    public function getAnnouncementTypes(): array
    {
        return array_map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'icon' => $type->icon(),
            'color' => $type->color(),
        ], AnnouncementType::cases());
    }

    public function getAnnouncementPriorities(): array
    {
        return array_map(fn($priority) => [
            'value' => $priority->value,
            'label' => $priority->label(),
            'color' => $priority->color(),
        ], AnnouncementPriority::cases());
    }
}
