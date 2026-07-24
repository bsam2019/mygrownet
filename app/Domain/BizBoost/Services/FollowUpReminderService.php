<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\FollowUpReminder;
use App\Domain\BizBoost\Repositories\FollowUpReminderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class FollowUpReminderService
{
    public function __construct(
        private FollowUpReminderRepositoryInterface $reminderRepo,
    ) {}

    public function getReminders(int $businessId, array $filters = []): array
    {
        return $this->reminderRepo->findByBusiness($businessId, $filters);
    }

    public function getStats(int $businessId): array
    {
        return [
            'total' => count($this->reminderRepo->findByBusiness($businessId)),
            'pending' => count($this->reminderRepo->findByBusiness($businessId, ['status' => 'pending'])),
            'overdue' => $this->countOverdue($businessId),
            'completed_today' => $this->countCompletedToday($businessId),
        ];
    }

    public function createReminder(array $data): FollowUpReminder
    {
        return $this->reminderRepo->save(FollowUpReminder::reconstitute($data));
    }

    public function updateReminder(int $id, array $data): ?FollowUpReminder
    {
        $existing = $this->reminderRepo->findById($id);
        if (!$existing) {
            return null;
        }
        $merged = array_merge($existing->toArray(), $data);
        $merged['id'] = $id;
        return $this->reminderRepo->save(FollowUpReminder::reconstitute($merged));
    }

    public function deleteReminder(int $id): void
    {
        $this->reminderRepo->delete($id);
    }

    public function complete(int $id, ?string $notes): ?FollowUpReminder
    {
        $existing = $this->reminderRepo->findById($id);
        if (!$existing) {
            return null;
        }

        $data = $existing->toArray();
        $data['id'] = $id;
        $data['status'] = 'completed';
        $data['completed_at'] = now()->toDateTimeString();
        $data['completion_notes'] = $notes ?? null;

        return $this->reminderRepo->save(FollowUpReminder::reconstitute($data));
    }

    public function snooze(int $id, string $snoozeUntil): ?FollowUpReminder
    {
        $existing = $this->reminderRepo->findById($id);
        if (!$existing) {
            return null;
        }

        $data = $existing->toArray();
        $data['id'] = $id;
        $data['due_date'] = $snoozeUntil;
        $data['remind_at'] = "{$snoozeUntil} {$existing->dueTime}:00";
        $data['notification_sent'] = false;
        $data['snoozed_count'] = $existing->snoozedCount + 1;

        return $this->reminderRepo->save(FollowUpReminder::reconstitute($data));
    }

    private function countOverdue(int $businessId): int
    {
        return DB::table('bizboost_follow_up_reminders')
            ->where('business_id', $businessId)
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->count();
    }

    private function countCompletedToday(int $businessId): int
    {
        return DB::table('bizboost_follow_up_reminders')
            ->where('business_id', $businessId)
            ->where('status', 'completed')
            ->whereDate('completed_at', now()->toDateString())
            ->count();
    }
}