<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class BusinessFundingEligibilityService
{
    /**
     * Evaluate business funding eligibility for Level 5 (Strategist) and Level 6 (Mentor) leaders.
     */
    public function evaluateEligibility(User $user): array
    {
        $currentLevelSlug = $user->current_professional_level ?? 'starter';
        $levelObj = DB::table('professional_levels')->where('slug', $currentLevelSlug)->first();
        $levelNum = (int) ($levelObj->level ?? 1);

        $isEligibleLevel = in_array($levelNum, [5, 6, 7]);

        $hasBusinessPlan = DB::table('business_plans')->where('user_id', $user->id)->exists();

        $existingApplication = DB::table('business_funding_applications')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        return [
            'is_eligible' => $isEligibleLevel,
            'current_level' => $levelNum,
            'level_title' => $levelObj->name ?? 'Level ' . $levelNum,
            'has_business_plan' => $hasBusinessPlan,
            'application' => $existingApplication,
            'check_list' => [
                'level_5_or_6' => $isEligibleLevel,
                'business_plan_completed' => $hasBusinessPlan,
                'financial_records_submitted' => false,
            ],
        ];
    }

    /**
     * Submit Business Funding Application.
     */
    public function submitApplication(User $user, array $data): array
    {
        $eval = $this->evaluateEligibility($user);
        if (!$eval['is_eligible']) {
            return [
                'success' => false,
                'message' => 'Business funding assessment is reserved for Level 5 (Strategist) and Level 6 (Mentor) leaders.',
            ];
        }

        $appId = DB::table('business_funding_applications')->insertGetId([
            'user_id' => $user->id,
            'level_achieved' => $eval['current_level'],
            'business_name' => $data['business_name'],
            'funding_purpose' => $data['funding_purpose'],
            'requested_amount' => $data['requested_amount'] ?? 0.00,
            'status' => 'submitted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Business funding application submitted for review!',
            'application_id' => $appId,
        ];
    }
}
