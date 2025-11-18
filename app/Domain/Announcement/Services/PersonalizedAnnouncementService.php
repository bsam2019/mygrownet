<?php

namespace App\Domain\Announcement\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Personalized Announcement Service
 * 
 * Generates dynamic, personalized announcements based on user data
 */
class PersonalizedAnnouncementService
{
    /**
     * Generate personalized announcements for a user
     */
    public function generateForUser(User $user): array
    {
        // Get dismissed announcement keys
        $dismissedKeys = \DB::table('personalized_announcement_dismissals')
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->pluck('announcement_key')
            ->toArray();

        $announcements = [];

        // 1. Tier Advancement Progress
        $tierProgress = $this->getTierAdvancementAnnouncement($user);
        if ($tierProgress) {
            $announcements[] = $tierProgress;
        }

        // 2. Starter Kit Promotion (if not purchased)
        if (!$user->has_starter_kit) {
            $announcements[] = $this->getStarterKitAnnouncement($user);
        }

        // 3. Recent Earnings Milestone
        $earningsMilestone = $this->getEarningsMilestoneAnnouncement($user);
        if ($earningsMilestone) {
            $announcements[] = $earningsMilestone;
        }

        // 4. Network Growth
        $networkGrowth = $this->getNetworkGrowthAnnouncement($user);
        if ($networkGrowth) {
            $announcements[] = $networkGrowth;
        }

        // 5. Activity Reminder (if inactive)
        $activityReminder = $this->getActivityReminderAnnouncement($user);
        if ($activityReminder) {
            $announcements[] = $activityReminder;
        }

        // 6. LGR Withdrawal Opportunity
        $lgrOpportunity = $this->getLgrWithdrawalAnnouncement($user);
        if ($lgrOpportunity) {
            $announcements[] = $lgrOpportunity;
        }

        // 7. Pending Actions
        $pendingActions = $this->getPendingActionsAnnouncement($user);
        if ($pendingActions) {
            $announcements[] = $pendingActions;
        }

        // Filter out dismissed announcements
        $announcements = array_filter($announcements, function ($announcement) use ($dismissedKeys) {
            $key = $this->extractAnnouncementKey($announcement['id']);
            return !in_array($key, $dismissedKeys);
        });

        return array_values($announcements); // Re-index array
    }

    /**
     * Extract announcement key from ID (remove user-specific suffix)
     */
    private function extractAnnouncementKey(string $id): string
    {
        // Remove user ID suffix (e.g., 'tier_progress_123' -> 'tier_progress')
        return preg_replace('/_\d+$/', '', $id);
    }

    /**
     * Dismiss a personalized announcement for a user
     */
    public function dismissAnnouncement(int $userId, string $announcementId, int $daysUntilExpiry = 7): void
    {
        $key = $this->extractAnnouncementKey($announcementId);
        
        // Use upsert to handle duplicate key constraint properly
        \DB::table('personalized_announcement_dismissals')->upsert(
            [
                [
                    'user_id' => $userId,
                    'announcement_key' => $key,
                    'dismissed_at' => now(),
                    'expires_at' => now()->addDays($daysUntilExpiry),
                ]
            ],
            ['user_id', 'announcement_key'], // Unique keys
            ['dismissed_at', 'expires_at'] // Columns to update
        );
    }

    /**
     * Tier advancement progress announcement
     */
    private function getTierAdvancementAnnouncement(User $user): ?array
    {
        $currentTier = $user->currentMembershipTier->name ?? 'Associate';
        $directReferrals = $user->directReferrals()->count();

        // Tier requirements (simplified - adjust based on your actual logic)
        $tierRequirements = [
            'Associate' => ['next' => 'Professional', 'referrals' => 3],
            'Professional' => ['next' => 'Senior', 'referrals' => 9],
            'Senior' => ['next' => 'Manager', 'referrals' => 27],
            'Manager' => ['next' => 'Director', 'referrals' => 81],
            'Director' => ['next' => 'Executive', 'referrals' => 243],
            'Executive' => ['next' => 'Ambassador', 'referrals' => 729],
        ];

        if (!isset($tierRequirements[$currentTier])) {
            return null; // Already at max tier
        }

        $requirement = $tierRequirements[$currentTier];
        $needed = $requirement['referrals'] - $directReferrals;

        // Only show if close to advancement (within 5 referrals)
        if ($needed > 0 && $needed <= 5) {
            return [
                'id' => 'tier_progress_' . $user->id,
                'title' => "Advance to {$requirement['next']} Level! 🎯",
                'message' => "You're {$needed} " . ($needed === 1 ? 'referral' : 'referrals') . " away from {$requirement['next']} level! Unlock higher commissions and exclusive benefits.",
                'type' => 'warning',
                'is_urgent' => false,
                'is_personalized' => true,
                'created_at' => now()->toISOString(),
            ];
        }

        return null;
    }

    /**
     * Starter kit promotion announcement
     */
    private function getStarterKitAnnouncement(User $user): array
    {
        return [
            'id' => 'starter_kit_' . $user->id,
            'title' => 'Unlock Your Full Potential! ⭐',
            'message' => 'Get your Starter Kit to access learning resources, shop credit, and enhanced earning opportunities. Starting at K500!',
            'type' => 'info',
            'is_urgent' => false,
            'is_personalized' => true,
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Recent earnings milestone announcement
     */
    private function getEarningsMilestoneAnnouncement(User $user): ?array
    {
        // Get total earnings from wallet transactions
        $totalEarnings = (float) ($user->total_earnings ?? 0);

        // Milestone thresholds
        $milestones = [100, 500, 1000, 5000, 10000];
        
        foreach ($milestones as $milestone) {
            // Check if just crossed this milestone
            if ($totalEarnings >= $milestone && $totalEarnings < ($milestone * 1.5)) {
                return [
                    'id' => 'earnings_milestone_' . $milestone . '_' . $user->id,
                    'title' => "Milestone Achieved! 🎉",
                    'message' => "Congratulations! You've earned K" . number_format($totalEarnings, 2) . " in total. Keep up the great work!",
                    'type' => 'success',
                    'is_urgent' => false,
                    'is_personalized' => true,
                    'created_at' => now()->toISOString(),
                ];
            }
        }

        return null;
    }

    /**
     * Network growth announcement
     */
    private function getNetworkGrowthAnnouncement(User $user): ?array
    {
        $totalNetwork = count($user->getDownlineMembers(7));
        
        // Network size milestones
        $milestones = [10, 25, 50, 100, 250, 500];
        
        foreach ($milestones as $milestone) {
            if ($totalNetwork >= $milestone) {
                // Check if recently crossed (within last 7 days)
                $recentMembers = \App\Models\User::whereIn('id', array_column($user->getDownlineMembers(7), 'id'))
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
                $recentGrowth = $recentMembers;

                if ($recentGrowth > 0 && $totalNetwork - $recentGrowth < $milestone) {
                    return [
                        'id' => 'network_growth_' . $milestone . '_' . $user->id,
                        'title' => "Network Milestone! 🌟",
                        'message' => "Your network has grown to {$totalNetwork} members! Your leadership is making an impact.",
                        'type' => 'success',
                        'is_urgent' => false,
                        'is_personalized' => true,
                        'created_at' => now()->toISOString(),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Activity reminder for inactive users
     */
    private function getActivityReminderAnnouncement(User $user): ?array
    {
        $lastLogin = $user->last_login_at ?? $user->created_at;
        $daysSinceLogin = now()->diffInDays($lastLogin);

        if ($daysSinceLogin >= 7 && $daysSinceLogin <= 14) {
            return [
                'id' => 'activity_reminder_' . $user->id,
                'title' => 'We Miss You! 👋',
                'message' => "You haven't logged in for {$daysSinceLogin} days. Check your earnings, team progress, and new opportunities!",
                'type' => 'info',
                'is_urgent' => false,
                'is_personalized' => true,
                'created_at' => now()->toISOString(),
            ];
        }

        return null;
    }

    /**
     * LGR withdrawal opportunity announcement
     */
    private function getLgrWithdrawalAnnouncement(User $user): ?array
    {
        $lgrBalance = (float) ($user->loyalty_points ?? 0);
        
        // Calculate withdrawable amount
        $lgrWithdrawablePercentage = $user->lgr_custom_withdrawable_percentage 
            ?? \App\Models\LgrSetting::get('lgr_max_cash_conversion', 40);
        $lgrAwardedTotal = (float) ($user->loyalty_points_awarded_total ?? 0);
        $lgrWithdrawnTotal = (float) ($user->loyalty_points_withdrawn_total ?? 0);
        $lgrMaxWithdrawable = ($lgrAwardedTotal * $lgrWithdrawablePercentage / 100) - $lgrWithdrawnTotal;
        $lgrWithdrawable = min($lgrBalance, max(0, $lgrMaxWithdrawable));

        // Only show if they have significant withdrawable LGR (>= 100)
        if ($lgrWithdrawable >= 100 && !($user->lgr_withdrawal_blocked ?? false)) {
            return [
                'id' => 'lgr_withdrawal_' . $user->id,
                'title' => 'LGR Points Available! 💰',
                'message' => "You have " . number_format($lgrWithdrawable, 0) . " LGR points available for withdrawal. Convert them to cash now!",
                'type' => 'success',
                'is_urgent' => false,
                'is_personalized' => true,
                'created_at' => now()->toISOString(),
            ];
        }

        return null;
    }

    /**
     * Pending actions announcement
     */
    private function getPendingActionsAnnouncement(User $user): ?array
    {
        // Check for pending withdrawals
        $pendingWithdrawals = \App\Models\WithdrawalRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        if ($pendingWithdrawals > 0) {
            return [
                'id' => 'pending_withdrawals_' . $user->id,
                'title' => 'Withdrawal Processing ⏳',
                'message' => "You have {$pendingWithdrawals} " . ($pendingWithdrawals === 1 ? 'withdrawal' : 'withdrawals') . " being processed. We'll notify you once completed.",
                'type' => 'info',
                'is_urgent' => false,
                'is_personalized' => true,
                'created_at' => now()->toISOString(),
            ];
        }

        return null;
    }
}
