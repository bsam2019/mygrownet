<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\FollowUpReminder;
use App\Domain\BizBoost\Repositories\FollowUpReminderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentFollowUpReminderRepository implements FollowUpReminderRepositoryInterface
{
    public function findById(int $id): ?FollowUpReminder
    {
        $row = DB::table('bizboost_follow_up_reminders')->where('id', $id)->first();
        return $row ? FollowUpReminder::reconstitute((array) $row) : null;
    }

    public function findByBusiness(int $businessId, array $filters = []): array
    {
        $query = DB::table('bizboost_follow_up_reminders')->where('business_id', $businessId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $rows = $query->orderBy('due_date')->orderBy('due_time')->get();
        return $rows->map(fn($r) => FollowUpReminder::reconstitute((array) $r))->toArray();
    }

    public function save(FollowUpReminder $entity): FollowUpReminder
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            DB::table('bizboost_follow_up_reminders')->where('id', $id)->update($data);
            return $this->findById($id);
        }

        $newId = DB::table('bizboost_follow_up_reminders')->insertGetId($data);
        return $this->findById($newId);
    }

    public function delete(int $id): void
    {
        DB::table('bizboost_follow_up_reminders')->where('id', $id)->delete();
    }
}