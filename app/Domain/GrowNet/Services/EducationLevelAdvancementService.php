<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class EducationLevelAdvancementService
{
    /**
     * Evaluate the Two-Gate Progression Model for a member.
     * Both PositionGate (LP, Time, Directs) AND EducationGate (Lessons, Workshops, Assessments) must pass.
     */
    public function evaluateAdvancement(User $user): array
    {
        $currentLevel = (int) ($user->current_professional_level_id ?? 1);
        $nextLevelNumber = min($currentLevel + 1, 7);

        // 1. Fetch requirements for next level from professional_levels
        $nextLevel = DB::table('professional_levels')->where('level', $nextLevelNumber)->first();
        if (!$nextLevel) {
            return [
                'current_level' => $currentLevel,
                'can_advance' => false,
                'reason' => 'Maximum level already achieved.',
            ];
        }

        // 2. Fetch user points (Life Points LP)
        $userPoints = DB::table('user_points')->where('user_id', $user->id)->first();
        $userLP = $userPoints ? $userPoints->lifetime_points : 0;

        // 3. Evaluate Position Gate
        $lpRequired = (int) ($nextLevel->lp_required ?? 0);
        $positionGatePassed = $userLP >= $lpRequired;

        // 4. Evaluate Education Gate
        $coursesRequired = $currentLevel; // Level N requires N courses completed
        $coursesCompleted = DB::table('assessment_attempts')
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->distinct('level')
            ->count('level');

        $educationGatePassed = $coursesCompleted >= $coursesRequired;

        // 5. Evaluate Workshop Attendance Gate
        $workshopsAttended = DB::table('workshop_attendance')
            ->where('user_id', $user->id)
            ->count();
        $workshopsRequired = max(1, $currentLevel - 1);
        $workshopGatePassed = $workshopsAttended >= $workshopsRequired;

        $canAdvance = $positionGatePassed && $educationGatePassed && $workshopGatePassed;

        if ($canAdvance && $currentLevel < $nextLevelNumber) {
            // Update user level
            DB::table('users')->where('id', $user->id)->update([
                'current_professional_level' => $nextLevel->slug,
                'level_achieved_at' => now(),
            ]);

            // Trigger physical reward allocation if applicable
            app(PhysicalRewardAllocationService::class)->allocateForLevel($user, $nextLevelNumber);
        }

        return [
            'current_level' => $currentLevel,
            'next_level' => $nextLevelNumber,
            'next_level_name' => $nextLevel->name,
            'can_advance' => $canAdvance,
            'position_gate' => [
                'passed' => $positionGatePassed,
                'current_lp' => $userLP,
                'required_lp' => $lpRequired,
                'progress_percent' => $lpRequired > 0 ? min(100, round(($userLP / $lpRequired) * 100, 1)) : 100,
            ],
            'education_gate' => [
                'passed' => $educationGatePassed,
                'completed_courses' => $coursesCompleted,
                'required_courses' => $coursesRequired,
                'progress_percent' => $coursesRequired > 0 ? min(100, round(($coursesCompleted / $coursesRequired) * 100, 1)) : 100,
            ],
            'workshop_gate' => [
                'passed' => $workshopGatePassed,
                'attended' => $workshopsAttended,
                'required' => $workshopsRequired,
            ],
        ];
    }
}
