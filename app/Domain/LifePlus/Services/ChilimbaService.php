<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaGroupModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaMemberModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaContributionModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaPayoutModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaLoanModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaLoanPaymentModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaMeetingModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaAuditLogModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaContributionTypeModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusChilimbaSpecialContributionModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChilimbaService
{
    // ==================== GROUPS ====================

    public function getGroups(int $userId): array
    {
        return LifePlusChilimbaGroupModel::where('user_id', $userId)
            ->where('is_active', true)
            ->withCount(['members', 'contributions'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($g) => $this->mapGroup($g, $userId))
            ->toArray();
    }

    public function getGroup(int $id, int $userId): ?array
    {
        $group = LifePlusChilimbaGroupModel::where('id', $id)
            ->where('user_id', $userId)
            ->with(['members' => fn($q) => $q->orderBy('position_in_queue')])
            ->first();

        return $group ? $this->mapGroupDetail($group, $userId) : null;
    }


    public function createGroup(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {
            $group = LifePlusChilimbaGroupModel::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'meeting_frequency' => $data['meeting_frequency'] ?? 'monthly',
                'meeting_day' => $data['meeting_day'] ?? null,
                'meeting_time' => $data['meeting_time'] ?? null,
                'meeting_location' => $data['meeting_location'] ?? null,
                'min_contribution' => $data['min_contribution'],
                'max_contribution' => $data['max_contribution'] ?? null,
                'initial_contribution' => $data['initial_contribution'] ?? null,
                'teacher_contribution' => $data['teacher_contribution'] ?? 0,
                'absence_penalty' => $data['absence_penalty'] ?? 0,
                'total_members' => $data['total_members'],
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'user_role' => $data['user_role'] ?? 'member',
                'is_active' => true,
            ]);

            // Add the creator as a member
            $this->addMember($group->id, $userId, [
                'name' => auth()->user()->name,
                'phone' => auth()->user()->phone ?? null,
                'position_in_queue' => 1,
            ]);

            $this->logAudit($group->id, $userId, 'create', 'group', $group->id, null, $group->toArray());

            return $this->mapGroup($group->fresh(), $userId);
        });
    }

    public function updateGroup(int $id, int $userId, array $data): ?array
    {
        $group = LifePlusChilimbaGroupModel::where('id', $id)->where('user_id', $userId)->first();
        if (!$group) return null;

        $oldValues = $group->toArray();
        $group->update($data);

        $this->logAudit($group->id, $userId, 'update', 'group', $group->id, $oldValues, $group->fresh()->toArray());

        return $this->mapGroup($group->fresh(), $userId);
    }

    public function deleteGroup(int $id, int $userId): bool
    {
        $group = LifePlusChilimbaGroupModel::where('id', $id)->where('user_id', $userId)->first();
        if (!$group) return false;

        $this->logAudit($group->id, $userId, 'delete', 'group', $group->id, $group->toArray(), null);
        $group->update(['is_active' => false]);

        return true;
    }


    // ==================== MEMBERS ====================

    public function getMembers(int $groupId, int $userId): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        return LifePlusChilimbaMemberModel::where('group_id', $groupId)
            ->where('is_active', true)
            ->orderBy('position_in_queue')
            ->get()
            ->map(fn($m) => $this->mapMember($m, $groupId))
            ->toArray();
    }

    public function addMember(int $groupId, int $userId, array $data): ?array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return null;

        $member = LifePlusChilimbaMemberModel::create([
            'group_id' => $groupId,
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'position_in_queue' => $data['position_in_queue'] ?? $this->getNextQueuePosition($groupId),
            'is_active' => true,
        ]);

        $this->logAudit($groupId, $userId, 'create', 'member', $member->id, null, $member->toArray());

        return $this->mapMember($member, $groupId);
    }

    public function updateMember(int $memberId, int $userId, array $data): ?array
    {
        $member = LifePlusChilimbaMemberModel::find($memberId);
        if (!$member || !$this->verifyGroupAccess($member->group_id, $userId)) return null;

        $oldValues = $member->toArray();
        $member->update($data);

        $this->logAudit($member->group_id, $userId, 'update', 'member', $member->id, $oldValues, $member->fresh()->toArray());

        return $this->mapMember($member->fresh(), $member->group_id);
    }

    public function removeMember(int $memberId, int $userId, string $reason): bool
    {
        $member = LifePlusChilimbaMemberModel::find($memberId);
        if (!$member || !$this->verifyGroupAccess($member->group_id, $userId)) return false;

        $this->logAudit($member->group_id, $userId, 'delete', 'member', $member->id, $member->toArray(), null, $reason);
        $member->update(['is_active' => false]);

        return true;
    }

    private function getNextQueuePosition(int $groupId): int
    {
        return LifePlusChilimbaMemberModel::where('group_id', $groupId)->max('position_in_queue') + 1 ?? 1;
    }


    // ==================== CONTRIBUTIONS ====================

    public function getContributions(int $groupId, int $userId, ?string $month = null): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        $query = LifePlusChilimbaContributionModel::where('group_id', $groupId)
            ->with(['member', 'recordedBy']);

        if ($month) {
            $date = Carbon::parse($month);
            $query->whereYear('contribution_date', $date->year)
                  ->whereMonth('contribution_date', $date->month);
        }

        return $query->orderBy('contribution_date', 'desc')
            ->get()
            ->map(fn($c) => $this->mapContribution($c))
            ->toArray();
    }

    public function getMemberContributions(int $memberId, int $userId): array
    {
        $member = LifePlusChilimbaMemberModel::find($memberId);
        if (!$member || !$this->verifyGroupAccess($member->group_id, $userId)) return [];

        return LifePlusChilimbaContributionModel::where('member_id', $memberId)
            ->with('recordedBy')
            ->orderBy('contribution_date', 'desc')
            ->get()
            ->map(fn($c) => $this->mapContribution($c))
            ->toArray();
    }

    public function recordContribution(int $groupId, int $userId, array $data): ?array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return null;

        // Validate amount based on contribution rules
        $isInitial = $data['is_initial'] ?? false;
        $maxAllowed = $isInitial && $group->initial_contribution 
            ? $group->initial_contribution 
            : ($group->max_contribution ?? $group->min_contribution * 2);
        
        if ($data['amount'] > $maxAllowed) {
            throw new \Exception("Amount exceeds maximum allowed (K" . number_format($maxAllowed) . ")");
        }

        // Check for duplicate
        $exists = LifePlusChilimbaContributionModel::where('member_id', $data['member_id'])
            ->whereDate('contribution_date', $data['contribution_date'])
            ->exists();

        if ($exists) {
            throw new \Exception('Contribution already recorded for this date');
        }

        $contribution = LifePlusChilimbaContributionModel::create([
            'group_id' => $groupId,
            'member_id' => $data['member_id'],
            'recorded_by' => $userId,
            'contribution_date' => $data['contribution_date'],
            'amount' => $data['amount'],
            'is_initial' => $data['is_initial'] ?? false,
            'penalty_amount' => $data['penalty_amount'] ?? 0,
            'penalty_reason' => $data['penalty_reason'] ?? null,
            'teacher_amount' => $data['teacher_amount'] ?? 0,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'receipt_number' => $data['receipt_number'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logAudit($groupId, $userId, 'create', 'contribution', $contribution->id, null, $contribution->toArray());

        return $this->mapContribution($contribution->load(['member', 'recordedBy']));
    }

    public function recordBulkContributions(int $groupId, int $userId, array $contributions): array
    {
        $results = [];
        foreach ($contributions as $data) {
            try {
                $results[] = $this->recordContribution($groupId, $userId, $data);
            } catch (\Exception $e) {
                $results[] = ['error' => $e->getMessage(), 'member_id' => $data['member_id']];
            }
        }
        return $results;
    }


    public function updateContribution(int $id, int $userId, array $data, string $reason): ?array
    {
        $contribution = LifePlusChilimbaContributionModel::find($id);
        if (!$contribution) return null;

        $group = $this->verifyGroupAccess($contribution->group_id, $userId);
        if (!$group) return null;

        // Check if edit is allowed (within 24 hours or user is secretary)
        $hoursOld = $contribution->created_at->diffInHours(now());
        if ($hoursOld > 24 && $group->user_role !== 'secretary') {
            throw new \Exception('Cannot edit contributions older than 24 hours. Request approval from secretary.');
        }

        $oldValues = $contribution->toArray();
        $contribution->update($data);

        $this->logAudit($contribution->group_id, $userId, 'update', 'contribution', $id, $oldValues, $contribution->fresh()->toArray(), $reason);

        return $this->mapContribution($contribution->fresh()->load(['member', 'recordedBy']));
    }

    public function deleteContribution(int $id, int $userId, string $reason): bool
    {
        $contribution = LifePlusChilimbaContributionModel::find($id);
        if (!$contribution) return false;

        $group = $this->verifyGroupAccess($contribution->group_id, $userId);
        if (!$group || $group->user_role !== 'secretary') return false;

        $this->logAudit($contribution->group_id, $userId, 'delete', 'contribution', $id, $contribution->toArray(), null, $reason);
        $contribution->delete();

        return true;
    }

    public function getContributionSummary(int $groupId, int $userId, ?string $month = null): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        $date = $month ? Carbon::parse($month) : now();
        $members = LifePlusChilimbaMemberModel::where('group_id', $groupId)->where('is_active', true)->get();

        $contributions = LifePlusChilimbaContributionModel::where('group_id', $groupId)
            ->whereYear('contribution_date', $date->year)
            ->whereMonth('contribution_date', $date->month)
            ->get()
            ->groupBy('member_id');

        $expected = $members->count() * $group->contribution_amount;
        $collected = $contributions->flatten()->sum('amount');

        $memberStatus = $members->map(function ($member) use ($contributions, $group) {
            $memberContribs = $contributions->get($member->id, collect());
            $total = $memberContribs->sum('amount');
            return [
                'id' => $member->id,
                'name' => $member->name,
                'expected' => (float) $group->contribution_amount,
                'paid' => (float) $total,
                'status' => $total >= $group->contribution_amount ? 'paid' : 'pending',
            ];
        });

        return [
            'month' => $date->format('Y-m'),
            'month_name' => $date->format('F Y'),
            'expected' => (float) $expected,
            'collected' => (float) $collected,
            'outstanding' => (float) max(0, $expected - $collected),
            'members' => $memberStatus->toArray(),
        ];
    }


    // ==================== PAYOUTS ====================

    public function getPayouts(int $groupId, int $userId): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        return LifePlusChilimbaPayoutModel::where('group_id', $groupId)
            ->with(['member', 'recordedBy'])
            ->orderBy('payout_date', 'desc')
            ->get()
            ->map(fn($p) => $this->mapPayout($p))
            ->toArray();
    }

    public function getPayoutQueue(int $groupId, int $userId): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        return LifePlusChilimbaMemberModel::where('group_id', $groupId)
            ->where('is_active', true)
            ->orderBy('position_in_queue')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'position' => $m->position_in_queue,
                'has_received' => $m->has_received_payout,
                'payout_date' => $m->payout_date?->format('Y-m-d'),
            ])
            ->toArray();
    }

    public function recordPayout(int $groupId, int $userId, array $data): ?array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return null;

        $payout = LifePlusChilimbaPayoutModel::create([
            'group_id' => $groupId,
            'member_id' => $data['member_id'],
            'recorded_by' => $userId,
            'payout_date' => $data['payout_date'],
            'amount' => $data['amount'],
            'cycle_number' => $data['cycle_number'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Update member status
        LifePlusChilimbaMemberModel::where('id', $data['member_id'])->update([
            'has_received_payout' => true,
            'payout_date' => $data['payout_date'],
        ]);

        $this->logAudit($groupId, $userId, 'create', 'payout', $payout->id, null, $payout->toArray());

        return $this->mapPayout($payout->load(['member', 'recordedBy']));
    }

    // ==================== LOANS ====================

    public function getLoans(int $groupId, int $userId): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        return LifePlusChilimbaLoanModel::where('group_id', $groupId)
            ->with(['member', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($l) => $this->mapLoan($l))
            ->toArray();
    }

    public function requestLoan(int $groupId, int $userId, array $data): ?array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return null;

        $loan = LifePlusChilimbaLoanModel::create([
            'group_id' => $groupId,
            'member_id' => $data['member_id'],
            'loan_amount' => $data['loan_amount'],
            'interest_rate' => $data['interest_rate'] ?? 10,
            'loan_date' => $data['loan_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'],
            'purpose' => $data['purpose'] ?? null,
            'status' => 'pending',
        ]);

        $this->logAudit($groupId, $userId, 'create', 'loan', $loan->id, null, $loan->toArray());

        return $this->mapLoan($loan->load('member'));
    }


    public function approveLoan(int $loanId, int $userId): ?array
    {
        $loan = LifePlusChilimbaLoanModel::find($loanId);
        if (!$loan) return null;

        $group = $this->verifyGroupAccess($loan->group_id, $userId);
        if (!$group || !in_array($group->user_role, ['secretary', 'treasurer'])) return null;

        $oldValues = $loan->toArray();
        $loan->update(['status' => 'active']);

        $this->logAudit($loan->group_id, $userId, 'approve', 'loan', $loanId, $oldValues, $loan->fresh()->toArray());

        return $this->mapLoan($loan->fresh()->load('member'));
    }

    public function recordLoanPayment(int $loanId, int $userId, array $data): ?array
    {
        $loan = LifePlusChilimbaLoanModel::find($loanId);
        if (!$loan) return null;

        $group = $this->verifyGroupAccess($loan->group_id, $userId);
        if (!$group) return null;

        $payment = LifePlusChilimbaLoanPaymentModel::create([
            'loan_id' => $loanId,
            'recorded_by' => $userId,
            'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'notes' => $data['notes'] ?? null,
        ]);

        // Update loan total repaid
        $loan->increment('total_repaid', $data['amount']);

        // Check if fully paid
        if ($loan->total_repaid >= $loan->total_to_repay) {
            $loan->update(['status' => 'paid']);
        }

        $this->logAudit($loan->group_id, $userId, 'create', 'loan', $payment->id, null, $payment->toArray());

        return $this->mapLoan($loan->fresh()->load(['member', 'payments']));
    }

    public function calculateLoanRepayment(float $amount, float $interestRate): array
    {
        $interest = $amount * ($interestRate / 100);
        return [
            'principal' => $amount,
            'interest_rate' => $interestRate,
            'interest_amount' => round($interest, 2),
            'total_repayment' => round($amount + $interest, 2),
        ];
    }

    // ==================== MEETINGS ====================

    public function getMeetings(int $groupId, int $userId): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        return LifePlusChilimbaMeetingModel::where('group_id', $groupId)
            ->with('createdBy')
            ->orderBy('meeting_date', 'desc')
            ->get()
            ->map(fn($m) => $this->mapMeeting($m))
            ->toArray();
    }

    public function recordMeeting(int $groupId, int $userId, array $data): ?array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return null;

        $meeting = LifePlusChilimbaMeetingModel::create([
            'group_id' => $groupId,
            'created_by' => $userId,
            'meeting_date' => $data['meeting_date'],
            'attendees' => $data['attendees'] ?? [],
            'total_collected' => $data['total_collected'] ?? null,
            'payout_given_to' => $data['payout_given_to'] ?? null,
            'loans_approved' => $data['loans_approved'] ?? [],
            'decisions' => $data['decisions'] ?? null,
            'next_meeting_date' => $data['next_meeting_date'] ?? null,
        ]);

        $this->logAudit($groupId, $userId, 'create', 'meeting', $meeting->id, null, $meeting->toArray());

        return $this->mapMeeting($meeting->load('createdBy'));
    }


    // ==================== SPECIAL CONTRIBUTIONS ====================

    public function getContributionTypes(int $groupId, int $userId): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        return LifePlusChilimbaContributionTypeModel::where('group_id', $groupId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'icon' => $t->icon,
                'default_amount' => $t->default_amount ? (float) $t->default_amount : null,
                'is_mandatory' => $t->is_mandatory,
            ])
            ->toArray();
    }

    public function createContributionType(int $groupId, int $userId, array $data): ?array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group || !in_array($group->user_role, ['secretary', 'treasurer'])) return null;

        $type = LifePlusChilimbaContributionTypeModel::create([
            'group_id' => $groupId,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? '💰',
            'default_amount' => $data['default_amount'] ?? null,
            'is_mandatory' => $data['is_mandatory'] ?? false,
            'is_active' => true,
        ]);

        $this->logAudit($groupId, $userId, 'create', 'contribution_type', $type->id, null, $type->toArray());

        return [
            'id' => $type->id,
            'name' => $type->name,
            'icon' => $type->icon,
            'default_amount' => $type->default_amount ? (float) $type->default_amount : null,
            'is_mandatory' => $type->is_mandatory,
        ];
    }

    public function getSpecialContributions(int $groupId, int $userId, ?int $typeId = null): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        $query = LifePlusChilimbaSpecialContributionModel::where('group_id', $groupId)
            ->with(['type', 'member', 'beneficiary', 'recordedBy']);

        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        return $query->orderBy('contribution_date', 'desc')
            ->get()
            ->map(fn($c) => $this->mapSpecialContribution($c))
            ->toArray();
    }

    public function recordSpecialContribution(int $groupId, int $userId, array $data): ?array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return null;

        $contribution = LifePlusChilimbaSpecialContributionModel::create([
            'group_id' => $groupId,
            'type_id' => $data['type_id'],
            'member_id' => $data['member_id'],
            'recorded_by' => $userId,
            'beneficiary_id' => $data['beneficiary_id'] ?? null,
            'beneficiary_name' => $data['beneficiary_name'] ?? null,
            'contribution_date' => $data['contribution_date'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'notes' => $data['notes'] ?? null,
        ]);

        $this->logAudit($groupId, $userId, 'create', 'special_contribution', $contribution->id, null, $contribution->toArray());

        return $this->mapSpecialContribution($contribution->load(['type', 'member', 'beneficiary', 'recordedBy']));
    }

    public function getSpecialContributionSummary(int $groupId, int $userId): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        $types = LifePlusChilimbaContributionTypeModel::where('group_id', $groupId)
            ->where('is_active', true)
            ->get();

        return $types->map(function ($type) use ($groupId) {
            $total = LifePlusChilimbaSpecialContributionModel::where('group_id', $groupId)
                ->where('type_id', $type->id)
                ->sum('amount');
            $count = LifePlusChilimbaSpecialContributionModel::where('group_id', $groupId)
                ->where('type_id', $type->id)
                ->count();

            return [
                'type_id' => $type->id,
                'name' => $type->name,
                'icon' => $type->icon,
                'total_collected' => (float) $total,
                'contribution_count' => $count,
            ];
        })->toArray();
    }

    private function mapSpecialContribution($contribution): array
    {
        return [
            'id' => $contribution->id,
            'type_id' => $contribution->type_id,
            'type_name' => $contribution->type->name ?? 'Unknown',
            'type_icon' => $contribution->type->icon ?? '💰',
            'member_id' => $contribution->member_id,
            'member_name' => $contribution->member->name ?? 'Unknown',
            'beneficiary_name' => $contribution->beneficiary?->name ?? $contribution->beneficiary_name ?? null,
            'contribution_date' => $contribution->contribution_date->format('Y-m-d'),
            'formatted_date' => $contribution->contribution_date->format('M d, Y'),
            'amount' => (float) $contribution->amount,
            'payment_method' => $contribution->payment_method,
            'notes' => $contribution->notes,
            'recorded_by' => $contribution->recordedBy->name ?? 'Unknown',
            'created_at' => $contribution->created_at->format('M d, Y H:i'),
        ];
    }

    // ==================== AUDIT & HELPERS ====================

    public function getAuditLog(int $groupId, int $userId, int $limit = 50): array
    {
        $group = $this->verifyGroupAccess($groupId, $userId);
        if (!$group) return [];

        return LifePlusChilimbaAuditLogModel::where('group_id', $groupId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'action' => $log->action_type,
                'entity' => $log->entity_type,
                'user' => $log->user->name ?? 'Unknown',
                'reason' => $log->reason,
                'created_at' => $log->created_at->format('M d, Y H:i'),
                'description' => $this->getAuditDescription($log),
            ])
            ->toArray();
    }

    private function logAudit(int $groupId, int $userId, string $action, string $entity, int $entityId, ?array $old, ?array $new, ?string $reason = null): void
    {
        LifePlusChilimbaAuditLogModel::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'action_type' => $action,
            'entity_type' => $entity,
            'entity_id' => $entityId,
            'old_values' => $old,
            'new_values' => $new,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function verifyGroupAccess(int $groupId, int $userId): ?LifePlusChilimbaGroupModel
    {
        return LifePlusChilimbaGroupModel::where('id', $groupId)->where('user_id', $userId)->first();
    }

    private function getAuditDescription($log): string
    {
        $user = $log->user->name ?? 'Someone';
        $action = match($log->action_type) {
            'create' => 'recorded',
            'update' => 'updated',
            'delete' => 'deleted',
            'approve' => 'approved',
            'reject' => 'rejected',
            default => $log->action_type,
        };
        $entity = match($log->entity_type) {
            'contribution' => 'a contribution',
            'payout' => 'a payout',
            'loan' => 'a loan',
            'member' => 'a member',
            'meeting' => 'meeting minutes',
            'group' => 'group settings',
            default => $log->entity_type,
        };
        return "{$user} {$action} {$entity}";
    }


    // ==================== MAPPERS ====================

    private function mapGroup($group, int $userId): array
    {
        $myMember = LifePlusChilimbaMemberModel::where('group_id', $group->id)
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('name', auth()->user()->name);
            })->first();

        $totalContributed = LifePlusChilimbaContributionModel::where('group_id', $group->id)
            ->where('member_id', $myMember?->id)
            ->sum('amount');

        return [
            'id' => $group->id,
            'name' => $group->name,
            'meeting_frequency' => $group->meeting_frequency,
            'meeting_day' => $group->meeting_day,
            'meeting_time' => $group->meeting_time?->format('H:i'),
            'meeting_location' => $group->meeting_location,
            'min_contribution' => (float) $group->min_contribution,
            'max_contribution' => $group->max_contribution ? (float) $group->max_contribution : null,
            'initial_contribution' => $group->initial_contribution ? (float) $group->initial_contribution : null,
            'teacher_contribution' => (float) ($group->teacher_contribution ?? 0),
            'absence_penalty' => (float) ($group->absence_penalty ?? 0),
            'contribution_amount' => (float) $group->min_contribution, // For backward compatibility
            'total_members' => $group->total_members,
            'start_date' => $group->start_date->format('Y-m-d'),
            'user_role' => $group->user_role,
            'is_secretary' => $group->user_role === 'secretary',
            'my_total_contributed' => (float) $totalContributed,
            'my_position' => $myMember?->position_in_queue,
            'next_meeting' => $this->calculateNextMeeting($group),
        ];
    }

    private function mapGroupDetail($group, int $userId): array
    {
        $base = $this->mapGroup($group, $userId);
        $base['members'] = $group->members->map(fn($m) => $this->mapMember($m, $group->id))->toArray();
        return $base;
    }

    private function mapMember($member, int $groupId): array
    {
        $totalContributed = LifePlusChilimbaContributionModel::where('member_id', $member->id)->sum('amount');
        $activeLoans = LifePlusChilimbaLoanModel::where('member_id', $member->id)
            ->whereIn('status', ['active', 'pending'])->sum('loan_amount');

        return [
            'id' => $member->id,
            'name' => $member->name,
            'phone' => $member->phone,
            'position_in_queue' => $member->position_in_queue,
            'has_received_payout' => $member->has_received_payout,
            'payout_date' => $member->payout_date?->format('Y-m-d'),
            'total_contributed' => (float) $totalContributed,
            'active_loans' => (float) $activeLoans,
            'is_active' => $member->is_active,
        ];
    }

    private function mapContribution($contribution): array
    {
        return [
            'id' => $contribution->id,
            'member_id' => $contribution->member_id,
            'member_name' => $contribution->member->name ?? 'Unknown',
            'contribution_date' => $contribution->contribution_date->format('Y-m-d'),
            'formatted_date' => $contribution->contribution_date->format('M d, Y'),
            'amount' => (float) $contribution->amount,
            'is_initial' => (bool) $contribution->is_initial,
            'penalty_amount' => (float) ($contribution->penalty_amount ?? 0),
            'penalty_reason' => $contribution->penalty_reason,
            'teacher_amount' => (float) ($contribution->teacher_amount ?? 0),
            'total_paid' => (float) $contribution->amount + (float) ($contribution->penalty_amount ?? 0) + (float) ($contribution->teacher_amount ?? 0),
            'payment_method' => $contribution->payment_method,
            'receipt_number' => $contribution->receipt_number,
            'notes' => $contribution->notes,
            'recorded_by' => $contribution->recordedBy->name ?? 'Unknown',
            'recorded_by_self' => $contribution->recorded_by === auth()->id(),
            'can_edit' => $contribution->created_at->diffInHours(now()) <= 24,
            'created_at' => $contribution->created_at->format('M d, Y H:i'),
        ];
    }


    private function mapPayout($payout): array
    {
        return [
            'id' => $payout->id,
            'member_id' => $payout->member_id,
            'member_name' => $payout->member->name ?? 'Unknown',
            'payout_date' => $payout->payout_date->format('Y-m-d'),
            'formatted_date' => $payout->payout_date->format('M d, Y'),
            'amount' => (float) $payout->amount,
            'cycle_number' => $payout->cycle_number,
            'notes' => $payout->notes,
            'recorded_by' => $payout->recordedBy->name ?? 'Unknown',
        ];
    }

    private function mapLoan($loan): array
    {
        $totalToRepay = $loan->loan_amount * (1 + $loan->interest_rate / 100);
        $remaining = max(0, $totalToRepay - $loan->total_repaid);

        return [
            'id' => $loan->id,
            'member_id' => $loan->member_id,
            'member_name' => $loan->member->name ?? 'Unknown',
            'loan_amount' => (float) $loan->loan_amount,
            'interest_rate' => (float) $loan->interest_rate,
            'total_to_repay' => round($totalToRepay, 2),
            'total_repaid' => (float) $loan->total_repaid,
            'remaining_balance' => round($remaining, 2),
            'loan_date' => $loan->loan_date->format('Y-m-d'),
            'due_date' => $loan->due_date->format('Y-m-d'),
            'formatted_due_date' => $loan->due_date->format('M d, Y'),
            'purpose' => $loan->purpose,
            'status' => $loan->status,
            'is_overdue' => $loan->status === 'active' && $loan->due_date->isPast(),
            'payments' => $loan->payments?->map(fn($p) => [
                'id' => $p->id,
                'amount' => (float) $p->amount,
                'payment_date' => $p->payment_date->format('M d, Y'),
                'payment_method' => $p->payment_method,
            ])->toArray() ?? [],
        ];
    }

    private function mapMeeting($meeting): array
    {
        return [
            'id' => $meeting->id,
            'meeting_date' => $meeting->meeting_date->format('Y-m-d'),
            'formatted_date' => $meeting->meeting_date->format('M d, Y'),
            'attendees' => $meeting->attendees ?? [],
            'total_collected' => (float) ($meeting->total_collected ?? 0),
            'payout_given_to' => $meeting->payout_given_to,
            'loans_approved' => $meeting->loans_approved ?? [],
            'decisions' => $meeting->decisions,
            'next_meeting_date' => $meeting->next_meeting_date?->format('Y-m-d'),
            'created_by' => $meeting->createdBy->name ?? 'Unknown',
        ];
    }

    private function calculateNextMeeting($group): ?string
    {
        $lastMeeting = LifePlusChilimbaMeetingModel::where('group_id', $group->id)
            ->orderBy('meeting_date', 'desc')
            ->first();

        if ($lastMeeting?->next_meeting_date) {
            return $lastMeeting->next_meeting_date->format('M d, Y');
        }

        // Calculate based on frequency
        $base = $lastMeeting?->meeting_date ?? $group->start_date;
        $next = match($group->meeting_frequency) {
            'weekly' => $base->copy()->addWeek(),
            'bi-weekly' => $base->copy()->addWeeks(2),
            'monthly' => $base->copy()->addMonth(),
            default => $base->copy()->addMonth(),
        };

        while ($next->isPast()) {
            $next = match($group->meeting_frequency) {
                'weekly' => $next->addWeek(),
                'bi-weekly' => $next->addWeeks(2),
                'monthly' => $next->addMonth(),
                default => $next->addMonth(),
            };
        }

        return $next->format('M d, Y');
    }
}
