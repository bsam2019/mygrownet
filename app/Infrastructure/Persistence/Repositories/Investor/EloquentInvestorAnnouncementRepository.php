<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorAnnouncement;
use App\Domain\Investor\Repositories\InvestorAnnouncementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestorAnnouncementModel;
use Illuminate\Support\Facades\DB;

class EloquentInvestorAnnouncementRepository implements InvestorAnnouncementRepositoryInterface
{
    public function findById(int $id): ?InvestorAnnouncement
    {
        $model = InvestorAnnouncementModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return InvestorAnnouncementModel::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findPublished(): array
    {
        return InvestorAnnouncementModel::published()
            ->orderByPriority()
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findActive(): array
    {
        return InvestorAnnouncementModel::active()
            ->orderByRaw("is_pinned DESC")
            ->orderByPriority()
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findPinned(): array
    {
        return InvestorAnnouncementModel::active()
            ->pinned()
            ->orderByPriority()
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByType(string $type): array
    {
        return InvestorAnnouncementModel::active()
            ->byType($type)
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function save(InvestorAnnouncement $announcement): InvestorAnnouncement
    {
        $data = $announcement->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($announcement->getId()) {
            $model = InvestorAnnouncementModel::findOrFail($announcement->getId());
            $model->update($data);
        } else {
            $model = InvestorAnnouncementModel::create($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return InvestorAnnouncementModel::destroy($id) > 0;
    }

    public function markAsRead(int $announcementId, int $investorAccountId): void
    {
        DB::table('investor_announcement_reads')->updateOrInsert(
            [
                'announcement_id' => $announcementId,
                'investor_account_id' => $investorAccountId,
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    public function isReadByInvestor(int $announcementId, int $investorAccountId): bool
    {
        return DB::table('investor_announcement_reads')
            ->where('announcement_id', $announcementId)
            ->where('investor_account_id', $investorAccountId)
            ->exists();
    }

    public function getReadCount(int $announcementId): int
    {
        return DB::table('investor_announcement_reads')
            ->where('announcement_id', $announcementId)
            ->count();
    }

    public function getUnreadForInvestor(int $investorAccountId): array
    {
        $readIds = DB::table('investor_announcement_reads')
            ->where('investor_account_id', $investorAccountId)
            ->pluck('announcement_id')
            ->toArray();

        return InvestorAnnouncementModel::active()
            ->whereNotIn('id', $readIds)
            ->orderByPriority()
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    private function toDomainEntity(InvestorAnnouncementModel $model): InvestorAnnouncement
    {
        return InvestorAnnouncement::fromArray($model->toArray());
    }
}
