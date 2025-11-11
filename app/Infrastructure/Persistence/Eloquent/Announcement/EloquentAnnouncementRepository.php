<?php

namespace App\Infrastructure\Persistence\Eloquent\Announcement;

use App\Domain\Announcement\Entities\Announcement;
use App\Domain\Announcement\Repositories\AnnouncementRepositoryInterface;
use App\Domain\Announcement\ValueObjects\AnnouncementType;
use App\Domain\Announcement\ValueObjects\TargetAudience;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent Implementation of Announcement Repository
 */
class EloquentAnnouncementRepository implements AnnouncementRepositoryInterface
{
    public function getActiveAnnouncements(): array
    {
        $models = AnnouncementModel::active()
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findById(int $id): ?Announcement
    {
        $model = AnnouncementModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function getReadAnnouncementIds(int $userId): array
    {
        return DB::table('announcement_reads')
            ->where('user_id', $userId)
            ->pluck('announcement_id')
            ->all();
    }

    public function markAsRead(int $announcementId, int $userId): void
    {
        DB::table('announcement_reads')->updateOrInsert(
            [
                'announcement_id' => $announcementId,
                'user_id' => $userId,
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    public function save(Announcement $announcement): void
    {
        // Implementation for saving (if needed for admin)
        // Not required for initial implementation
    }

    /**
     * Convert Eloquent model to Domain Entity
     */
    private function toDomainEntity(AnnouncementModel $model): Announcement
    {
        return Announcement::create(
            id: $model->id,
            title: $model->title,
            message: $model->message,
            type: AnnouncementType::fromString($model->type),
            targetAudience: TargetAudience::fromString($model->target_audience),
            tierFilter: $model->tier_filter,
            isActive: $model->is_active,
            startsAt: $model->starts_at ? DateTimeImmutable::createFromMutable($model->starts_at) : null,
            expiresAt: $model->expires_at ? DateTimeImmutable::createFromMutable($model->expires_at) : null,
            createdBy: $model->created_by,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at)
        );
    }
}
