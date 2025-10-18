<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log investment creation
     */
    public function logInvestmentCreated(Model $investment, float $amount): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_INVESTMENT_CREATED,
            $investment,
            Auth::id(),
            null,
            $investment->toArray(),
            $amount,
            $investment->reference ?? null,
            [
                'tier_id' => $investment->investment_tier_id ?? null,
                'referrer_id' => $investment->referrer_id ?? null,
            ]
        );
    }

    /**
     * Log investment update
     */
    public function logInvestmentUpdated(Model $investment, array $oldValues, array $newValues): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_INVESTMENT_UPDATED,
            $investment,
            Auth::id(),
            $oldValues,
            $newValues,
            $investment->amount ?? null,
            $investment->reference ?? null
        );
    }

    /**
     * Log withdrawal request
     */
    public function logWithdrawalRequested(Model $withdrawal, float $amount): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            $withdrawal,
            Auth::id(),
            null,
            $withdrawal->toArray(),
            $amount,
            $withdrawal->reference ?? null,
            [
                'type' => $withdrawal->type ?? null,
                'penalty_amount' => $withdrawal->penalty_amount ?? null,
                'net_amount' => $withdrawal->net_amount ?? null,
            ]
        );
    }

    /**
     * Log withdrawal approval
     */
    public function logWithdrawalApproved(Model $withdrawal, int $approvedBy): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_WITHDRAWAL_APPROVED,
            $withdrawal,
            $approvedBy,
            ['status' => 'pending'],
            ['status' => 'approved'],
            $withdrawal->net_amount ?? null,
            $withdrawal->reference ?? null,
            [
                'approved_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Log withdrawal rejection
     */
    public function logWithdrawalRejected(Model $withdrawal, int $rejectedBy, string $reason): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_WITHDRAWAL_REJECTED,
            $withdrawal,
            $rejectedBy,
            ['status' => 'pending'],
            ['status' => 'rejected'],
            $withdrawal->amount ?? null,
            $withdrawal->reference ?? null,
            [
                'rejection_reason' => $reason,
                'rejected_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Log commission payment
     */
    public function logCommissionPaid(Model $commission, float $amount, int $recipientId): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_COMMISSION_PAID,
            $commission,
            $recipientId,
            null,
            $commission->toArray(),
            $amount,
            $commission->reference ?? null,
            [
                'commission_type' => $commission->type ?? null,
                'level' => $commission->level ?? null,
                'source_investment_id' => $commission->investment_id ?? null,
            ]
        );
    }

    /**
     * Log profit distribution
     */
    public function logProfitDistributed(Model $distribution, float $amount, int $recipientId): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_PROFIT_DISTRIBUTED,
            $distribution,
            $recipientId,
            null,
            $distribution->toArray(),
            $amount,
            $distribution->reference ?? null,
            [
                'distribution_type' => $distribution->type ?? null,
                'period' => $distribution->period ?? null,
            ]
        );
    }

    /**
     * Log tier upgrade
     */
    public function logTierUpgraded(Model $user, int $fromTierId, int $toTierId): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_TIER_UPGRADED,
            $user,
            $user->id,
            ['investment_tier_id' => $fromTierId],
            ['investment_tier_id' => $toTierId],
            null,
            null,
            [
                'from_tier' => $fromTierId,
                'to_tier' => $toTierId,
                'upgraded_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Log login attempt
     */
    public function logLoginAttempt(string $email, bool $successful, ?int $userId = null): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_LOGIN_ATTEMPT,
            null,
            $userId,
            null,
            null,
            null,
            null,
            [
                'email' => $email,
                'successful' => $successful,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );
    }

    /**
     * Log password change
     */
    public function logPasswordChanged(int $userId): void
    {
        AuditLog::logEvent(
            AuditLog::EVENT_PASSWORD_CHANGED,
            null,
            $userId,
            null,
            null,
            null,
            null,
            [
                'changed_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Get financial audit trail for a user
     */
    public function getFinancialAuditTrail(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AuditLog::forUser($userId)
            ->financial()
            ->orderBy('created_at', 'desc');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get()->toArray();
    }

    /**
     * Get audit summary for admin dashboard
     */
    public function getAuditSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $query = AuditLog::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $totalEvents = $query->count();
        $financialEvents = $query->clone()->financial()->count();
        $totalAmount = $query->clone()->financial()->sum('amount');

        $eventsByType = $query->clone()
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();

        return [
            'total_events' => $totalEvents,
            'financial_events' => $financialEvents,
            'total_amount' => $totalAmount,
            'events_by_type' => $eventsByType,
        ];
    }
}